<template>
  <div ref="containerRef" class="fixed inset-0 z-50 overflow-hidden">
    <!-- Background with blur effect -->
    <div 
      ref="backgroundRef"
      class="absolute inset-0 transition-all duration-3000"
      :class="{ 'blur-sm': currentScene > 0 }"
      :style="{ 
        background: 'linear-gradient(135deg, #0a0f1a 0%, #1a2332 50%, #0a0f1a 100%)',
        backgroundSize: 'cover',
        backgroundPosition: 'center'
      }"
    >
      <!-- Animated particles background -->
      <canvas ref="particlesCanvas" class="absolute inset-0 w-full h-full"></canvas>
      
      <!-- Holographic grid -->
      <div 
        class="absolute inset-0 opacity-[0.08] transition-opacity duration-2000"
        :style="{
          backgroundImage: `linear-gradient(to right, rgba(53, 167, 255, 0.15) 1px, transparent 1px), linear-gradient(to bottom, rgba(53, 167, 255, 0.15) 1px, transparent 1px)`,
          backgroundSize: '80px 80px',
          transform: 'perspective(1000px) rotateX(60deg)'
        }"
      />
    </div>

    <!-- Three.js canvas container -->
    <div ref="canvasWrapper" class="absolute inset-0"></div>

    <!-- Text logo "Name" - SCENE 1 -->
    <Motion
      tag="div"
      ref="nameTextRef"
      class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-20 pointer-events-none"
      :initial="{ opacity: 0, scale: 0.8 }"
      :animate="nameTextAnimate"
      :transition="{ duration: 1.5, ease: 'easeOut' }"
      :style="{ 
        filter: `drop-shadow(0 0 30px rgba(53, 167, 255, ${nameGlow})) drop-shadow(0 0 60px rgba(56, 97, 140, ${nameGlow * 0.6}))` 
      }"
    >
      <img 
        :src="nameImagePath" 
        alt="BitChest Logo Text" 
        class="h-24 md:h-32 lg:h-40 w-auto object-contain transition-all duration-1000"
        :style="{ opacity: nameOpacity }"
      />
    </Motion>

    <!-- Button "Explore Future" - SCENE 4 -->
    <Motion
      tag="button"
      ref="buttonRef"
      @click="handleExploreClick"
      class="absolute bottom-16 md:bottom-20 left-1/2 transform -translate-x-1/2 z-30 group cursor-pointer"
      :style="buttonStyle"
      :initial="{ opacity: 0, y: 50, scale: 0.8 }"
      :animate="buttonAnimate"
      :transition="{ duration: 1, delay: 0, ease: 'easeOut' }"
      :while-hover="{ scale: 1.1, y: -5 }"
      :while-tap="{ scale: 0.95 }"
    >
      <div class="relative px-10 py-5 rounded-2xl font-bold text-lg md:text-xl text-white backdrop-blur-xl transition-all duration-500 overflow-hidden"
           :style="{ border: '2px solid rgba(53, 167, 255, 0.5)' }">
        
        <!-- Animated gradient border background -->
        <div 
          class="absolute inset-0 rounded-2xl opacity-100 pointer-events-none"
          :style="{
            background: 'linear-gradient(90deg, var(--blue), var(--blue-dark), var(--blue))',
            backgroundSize: '200% 100%',
            animation: 'gradient-border 3s linear infinite',
            zIndex: 0
          }"
        ></div>
        
        <!-- Button background -->
        <div 
          class="absolute inset-[2px] rounded-2xl pointer-events-none"
          :style="{ 
            background: 'rgba(26, 35, 50, 0.8)',
            zIndex: 1
          }"
        ></div>
        
        <!-- Text content - must be above all layers -->
        <span class="relative z-20 flex items-center gap-3">
          <span>Explorer le Futur</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:translate-x-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
          </svg>
        </span>
        
        <!-- Shine effect on hover -->
        <div class="absolute inset-0 rounded-2xl -translate-x-full group-hover:translate-x-full transition-transform duration-1000 bg-gradient-to-r from-transparent via-white/20 to-transparent pointer-events-none z-15"></div>
        
        <!-- Pulsing glow effect -->
        <div 
          class="absolute -inset-4 rounded-2xl opacity-50 blur-xl transition-all duration-500 group-hover:opacity-100 pointer-events-none z-0"
          :style="{ 
            background: 'radial-gradient(circle, rgba(53, 167, 255, 0.6), rgba(56, 97, 140, 0.3), transparent)',
            animation: 'pulse-glow 2s ease-in-out infinite'
          }"
        ></div>
        
        <!-- Inner glow on hover -->
        <div 
          class="absolute inset-[2px] rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none z-10"
          :style="{ 
            background: 'linear-gradient(135deg, rgba(53, 167, 255, 0.15), rgba(56, 97, 140, 0.1))',
            boxShadow: 'inset 0 0 30px rgba(53, 167, 255, 0.2)'
          }"
        ></div>
      </div>
    </Motion>

    <!-- Loading overlay -->
    <div v-if="loading" class="absolute inset-0 flex items-center justify-center z-50 bg-black/80">
      <div class="text-white text-xl">Loading 3D assets...</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { Motion } from '@motionone/vue';
import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls';
import nameImagePath from '../assets/Name.png';
import boxImagePath from '../assets/Careaux.png';

const emit = defineEmits<{
  end: [];
}>();

// Refs
const containerRef = ref<HTMLElement | null>(null);
const canvasWrapper = ref<HTMLElement | null>(null);
const backgroundRef = ref<HTMLElement | null>(null);
const particlesCanvas = ref<HTMLCanvasElement | null>(null);
const nameTextRef = ref<HTMLElement | null>(null);
const buttonRef = ref<HTMLElement | null>(null);

// Scene state
const currentScene = ref(0);
const loading = ref(true);
const nameOpacity = ref(0);
const nameGlow = ref(0);

// Animation states
const nameTextAnimate = computed(() => {
  if (currentScene.value >= 1) {
    return { opacity: 1, scale: 1 };
  }
  return { opacity: 0, scale: 0.8 };
});

const buttonAnimate = computed(() => {
  if (currentScene.value >= 3) {
    return { opacity: 1, y: 0, scale: 1 };
  }
  return { opacity: 0, y: 50, scale: 0.8 };
});

const buttonStyle = {
  boxShadow: '0 0 40px rgba(53, 167, 255, 0.4), 0 0 30px rgba(56, 97, 140, 0.3), 0 10px 40px rgba(0, 0, 0, 0.5)'
};

// Three.js setup
let renderer: THREE.WebGLRenderer | null = null;
let scene: THREE.Scene | null = null;
let camera: THREE.PerspectiveCamera | null = null;
let controls: OrbitControls | null = null;
let boxMesh: THREE.Mesh | null = null;
let nameSprite: THREE.Sprite | null = null;
let particles: THREE.Points | null = null;
let animationFrameId: number | null = null;

  // Scene timeline - reduced durations for faster intro
  const sceneDuration = [1500, 3000, 2000, 2500]; // ms per scene - faster pacing
let startTime = 0;
let sceneStartTime = 0;

// Camera animation targets - smoother movements
const cameraPositions = [
  { x: 0, y: 0, z: 10 }, // Scene 1: far, centered
  { x: 0, y: 2, z: 7 }, // Scene 2: above, closer
  { x: 0, y: 0.8, z: 6 }, // Scene 3: middle, close
  { x: 0, y: 0, z: 5.5 } // Scene 4: final position
];

const cameraLookAt = [
  { x: 0, y: 0, z: 0 }, // Scene 1: center
  { x: 0, y: 0.5, z: 0 }, // Scene 2: above center
  { x: 0, y: 0, z: 0 }, // Scene 3: center
  { x: 0, y: 0, z: 0 } // Scene 4: center
];

const initThree = () => {
  if (!canvasWrapper.value) return;

  // Renderer
  renderer = new THREE.WebGLRenderer({ 
    antialias: true, 
    alpha: true,
    powerPreference: 'high-performance'
  });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  renderer.setSize(canvasWrapper.value.clientWidth, canvasWrapper.value.clientHeight);
  renderer.setClearColor(0x000000, 0);
  renderer.shadowMap.enabled = true;
  renderer.shadowMap.type = THREE.PCFSoftShadowMap;
  canvasWrapper.value.appendChild(renderer.domElement);

  // Scene
  scene = new THREE.Scene();

  // Camera
  const aspect = canvasWrapper.value.clientWidth / canvasWrapper.value.clientHeight;
  camera = new THREE.PerspectiveCamera(50, aspect, 0.1, 1000);
  camera.position.set(cameraPositions[0].x, cameraPositions[0].y, cameraPositions[0].z);

  // Controls (disabled by default, only for camera movement)
  controls = new OrbitControls(camera, renderer.domElement);
  controls.enabled = false;
  controls.enableDamping = true;
  controls.dampingFactor = 0.05;

  // Lights - Brand color palette
  const ambientLight = new THREE.AmbientLight(0xffffff, 0.3);
  scene.add(ambientLight);

  // Main directional light (blue)
  const mainLight = new THREE.DirectionalLight(0x35a7ff, 1.2);
  mainLight.position.set(5, 5, 5);
  mainLight.castShadow = true;
  mainLight.shadow.mapSize.width = 2048;
  mainLight.shadow.mapSize.height = 2048;
  scene.add(mainLight);

  // Accent light (blue-dark)
  const accentLight = new THREE.DirectionalLight(0x38618c, 0.8);
  accentLight.position.set(-5, 3, -5);
  scene.add(accentLight);

  // Point light for rim lighting
  const pointLight = new THREE.PointLight(0x35a7ff, 0.6, 100);
  pointLight.position.set(0, 0, 5);
  scene.add(pointLight);

  // Load box texture and create 3D box
  const textureLoader = new THREE.TextureLoader();
  textureLoader.load(boxImagePath, (boxTexture) => {
    const boxGeometry = new THREE.BoxGeometry(1.5, 1.5, 1.5);
    const boxMaterial = new THREE.MeshStandardMaterial({
      map: boxTexture,
      metalness: 0.8,
      roughness: 0.2,
      emissive: 0x000000,
      emissiveIntensity: 0,
      transparent: true,
      alphaTest: 0.1
    });
    boxMesh = new THREE.Mesh(boxGeometry, boxMaterial);
    boxMesh.castShadow = true;
    boxMesh.receiveShadow = true;
    boxMesh.position.set(0, 3, 0); // Start above
    boxMesh.scale.set(0, 0, 0); // Start invisible
    scene!.add(boxMesh);
  });

  // Create floating particles
  const particleCount = 500;
  const particleGeometry = new THREE.BufferGeometry();
  const positions = new Float32Array(particleCount * 3);
  const colors = new Float32Array(particleCount * 3);
  
  for (let i = 0; i < particleCount; i++) {
    const i3 = i * 3;
    positions[i3] = (Math.random() - 0.5) * 20;
    positions[i3 + 1] = (Math.random() - 0.5) * 20;
    positions[i3 + 2] = (Math.random() - 0.5) * 20;

    // Brand colors (blue and blue-dark)
    const colorChoice = Math.random() > 0.5 ? [0.21, 0.65, 1.0] : [0.22, 0.38, 0.55]; // #35A7FF or #38618C
    colors[i3] = colorChoice[0];
    colors[i3 + 1] = colorChoice[1];
    colors[i3 + 2] = colorChoice[2];
  }

  particleGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
  particleGeometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));

  const particleMaterial = new THREE.PointsMaterial({
    size: 0.1,
    transparent: true,
    opacity: 0.6,
    vertexColors: true,
    blending: THREE.AdditiveBlending,
    sizeAttenuation: true
  });

  particles = new THREE.Points(particleGeometry, particleMaterial);
  scene.add(particles);

  loading.value = false;
  startTime = Date.now();
  sceneStartTime = startTime;

  animate();
};

const animate = () => {
  if (!renderer || !scene || !camera) return;

  const elapsed = Date.now() - startTime;
  const sceneElapsed = Date.now() - sceneStartTime;

  // Update current scene based on timeline
  let accumulatedTime = 0;
  for (let i = 0; i < sceneDuration.length; i++) {
    if (elapsed < accumulatedTime + sceneDuration[i]) {
      if (currentScene.value !== i) {
        currentScene.value = i;
        sceneStartTime = Date.now();
      }
      break;
    }
    accumulatedTime += sceneDuration[i];
  }

  // Scene-specific animations
  switch (currentScene.value) {
    case 0: // Scene 1: Build-up intro - Enhanced
      {
        const progress = Math.min(sceneElapsed / sceneDuration[0], 1);
        const eased = easeOutCubic(progress);
        
        // Camera dolly-in - smoother with easing
        camera.position.z = THREE.MathUtils.lerp(cameraPositions[0].z, cameraPositions[1].z, eased);
        camera.position.y = THREE.MathUtils.lerp(cameraPositions[0].y, cameraPositions[1].y, eased * 0.6);
        
        // Name text fade-in with scale and glow
        const textProgress = Math.min(progress * 1.5, 1);
        const textEased = easeOutBack(textProgress);
        nameOpacity.value = textEased;
        nameGlow.value = textEased * 0.9;
        
        // Animate particles - more dynamic
        if (particles) {
          particles.rotation.y += 0.002;
          particles.rotation.x += 0.001;
          
          // Particles move towards center
          const positionsAttr = particles.geometry.attributes.position as THREE.BufferAttribute;
          const arr = positionsAttr.array as Float32Array;
          for (let i = 0; i < arr.length; i += 3) {
            const dist = Math.sqrt(arr[i] * arr[i] + arr[i + 1] * arr[i + 1] + arr[i + 2] * arr[i + 2]);
            if (dist > 0.1) {
              arr[i] *= 0.999;
              arr[i + 1] *= 0.999;
              arr[i + 2] *= 0.999;
            }
          }
          positionsAttr.needsUpdate = true;
        }
      }
      break;

    case 1: // Scene 2: Box rotation above name - Enhanced
      {
        const progress = Math.min(sceneElapsed / sceneDuration[1], 1);
        const eased = easeInOutQuad(progress);
        
        // Camera circles around box - more cinematic
        const angle = eased * Math.PI * 1.2; // Full circle + extra
        const radius = 2.5;
        camera.position.x = Math.sin(angle) * radius;
        camera.position.y = THREE.MathUtils.lerp(cameraPositions[1].y, cameraPositions[2].y, eased) + Math.sin(angle * 0.5) * 0.3;
        camera.position.z = Math.cos(angle) * radius + 5;
        camera.lookAt(0, 1.2, 0);
        
        // Box appears with bounce and rotates
        if (boxMesh) {
          const scaleProgress = Math.min(progress * 1.5, 1);
          const scaleEased = easeOutBack(scaleProgress);
          const scale = scaleEased;
          boxMesh.scale.set(scale, scale, scale);
          
          // Multiple rotations for dramatic effect
          boxMesh.rotation.y = eased * Math.PI * 3; // 1.5 full rotations
          boxMesh.rotation.x = Math.sin(progress * Math.PI * 2) * 0.15; // Slight tilt
          
          // Smooth floating motion
          boxMesh.position.y = 2 + Math.sin(progress * Math.PI * 4) * 0.15;
          boxMesh.position.z = Math.sin(progress * Math.PI * 2) * 0.1;
          
          // Dynamic lighting with color transitions
          const lightIntensity = Math.sin(progress * Math.PI * 6) * 0.15 + 0.1;
          (boxMesh.material as THREE.MeshStandardMaterial).emissiveIntensity = lightIntensity;
          
          // Color shifts between brand colors
          const colorMix = Math.sin(progress * Math.PI * 2) * 0.5 + 0.5;
          (boxMesh.material as THREE.MeshStandardMaterial).emissive.setHex(
            THREE.MathUtils.lerp(0x38618c, 0x35a7ff, colorMix)
          );
        }
        
        // Particles continue with more energy
        if (particles) {
          particles.rotation.y += 0.003;
          particles.rotation.z += 0.001;
        }
      }
      break;

    case 2: // Scene 3: Return to original position - Enhanced
      {
        const progress = Math.min(sceneElapsed / sceneDuration[2], 1);
        const eased = easeInOutExpo(progress);
        
        // Camera returns to center - smooth arc movement
        const arcProgress = Math.sin(eased * Math.PI / 2);
        camera.position.x = THREE.MathUtils.lerp(cameraPositions[1].x, cameraPositions[3].x, eased);
        camera.position.y = THREE.MathUtils.lerp(cameraPositions[2].y, cameraPositions[3].y, eased) - arcProgress * 0.3;
        camera.position.z = THREE.MathUtils.lerp(cameraPositions[2].z, cameraPositions[3].z, eased);
        camera.lookAt(0, 0.4, 0);
        
        // Box descends with smooth deceleration
        if (boxMesh) {
          // Rotation slows to a stop with easing
          const rotationSlowdown = 1 - Math.pow(progress, 3);
          boxMesh.rotation.y = Math.PI * 3 + rotationSlowdown * Math.PI * 0.5;
          boxMesh.rotation.x = THREE.MathUtils.lerp(0.15, 0, eased * 1.2);
          
          // Descend with bounce at the end
          let yPos;
          if (progress < 0.9) {
            yPos = THREE.MathUtils.lerp(2, 0.85, eased);
          } else {
            const bounceProgress = (progress - 0.9) / 0.1;
            const bounceEased = 1 - Math.pow(1 - bounceProgress, 3);
            yPos = 0.85 - Math.sin(bounceEased * Math.PI) * 0.1; // Bounce effect
          }
          boxMesh.position.y = yPos;
          
          // Light pulse when locking into place - more dramatic
          if (progress > 0.7) {
            const pulseProgress = (progress - 0.7) / 0.3;
            const pulse = Math.sin(pulseProgress * Math.PI * 8) * (1 - pulseProgress) * 0.5;
            (boxMesh.material as THREE.MeshStandardMaterial).emissiveIntensity = pulse;
            (boxMesh.material as THREE.MeshStandardMaterial).emissive.setHex(
              THREE.MathUtils.lerp(0x35a7ff, 0x38618c, pulseProgress)
            );
          }
        }
      }
      break;

    case 3: // Scene 4: Full logo reveal + UI button - Enhanced
      {
        const progress = Math.min(sceneElapsed / sceneDuration[3], 1);
        const eased = easeOutCubic(progress);
        
        // Camera final position - subtle zoom in with breathing effect
        const zoomAmount = eased * 0.8;
        const breathAmount = Math.sin((sceneElapsed / 2000) * Math.PI * 2) * 0.05;
        camera.position.z = cameraPositions[3].z - zoomAmount + breathAmount;
        camera.position.y = cameraPositions[3].y + Math.sin((sceneElapsed / 3000) * Math.PI * 2) * 0.03;
        
        // Box final position with polished idle animation
        if (boxMesh) {
          boxMesh.rotation.y = Math.PI * 2.25; // Final rotation locked
          boxMesh.rotation.x = 0; // Reset tilt
          
          // Final position with smooth settle
          const finalY = 0.75 + (1 - eased) * 0.1; // Smooth settle
          
          // Elegant idle animation (breathing + slight rotation)
          const idleTime = sceneElapsed / 1000;
          const idleFloat = Math.sin(idleTime * 1.5) * 0.015;
          const idleRotZ = Math.sin(idleTime * 1.2) * 0.03;
          const idleRotY = Math.sin(idleTime * 0.8) * 0.02;
          
          boxMesh.position.y = finalY + idleFloat;
          boxMesh.rotation.z = idleRotZ;
          boxMesh.rotation.y = Math.PI * 2.25 + idleRotY;
          
          // Gentle glow pulse
          const glowPulse = Math.sin(idleTime * 2) * 0.05 + 0.1;
          (boxMesh.material as THREE.MeshStandardMaterial).emissiveIntensity = glowPulse;
          (boxMesh.material as THREE.MeshStandardMaterial).emissive.setHex(0x35a7ff);
        }
        
        // Name text subtle animation in sync
        if (nameTextRef.value) {
          const nameFloat = Math.sin((sceneElapsed / 1000) * 1.5) * 2;
          nameGlow.value = 0.8 + Math.sin((sceneElapsed / 1000) * 2) * 0.2;
        }
      }
      
      // After all scenes complete, wait then emit end
      if (elapsed >= accumulatedTime + 1000) {
        setTimeout(() => {
          emit('end');
        }, 1500);
      }
      break;
  }

  // Update controls (for smooth camera movement)
  if (controls) {
    controls.update();
  }

  // Rotate particles continuously
  if (particles) {
    particles.rotation.y += 0.001;
  }

  renderer.render(scene, camera);
  animationFrameId = requestAnimationFrame(animate);
};

// Enhanced easing functions for smoother animations
const easeInOutCubic = (t: number): number => {
  return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
};

const easeOutCubic = (t: number): number => {
  return 1 - Math.pow(1 - t, 3);
};

const easeOutBack = (t: number): number => {
  const c1 = 1.70158;
  const c3 = c1 + 1;
  return 1 + c3 * Math.pow(t - 1, 3) + c1 * Math.pow(t - 1, 2);
};

const easeInOutQuad = (t: number): number => {
  return t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2;
};

const easeInOutExpo = (t: number): number => {
  return t === 0 ? 0 : t === 1 ? 1 : t < 0.5 
    ? Math.pow(2, 20 * t - 10) / 2 
    : (2 - Math.pow(2, -20 * t + 10)) / 2;
};

const handleExploreClick = () => {
  emit('end');
};

// Handle resize
const handleResize = () => {
  if (!canvasWrapper.value || !camera || !renderer) return;
  const width = canvasWrapper.value.clientWidth;
  const height = canvasWrapper.value.clientHeight;
  camera.aspect = width / height;
  camera.updateProjectionMatrix();
  renderer.setSize(width, height);
};

// Particles canvas animation (2D background particles)
const initParticlesCanvas = () => {
  const canvas = particlesCanvas.value;
  if (!canvas) return;
  
  const ctx = canvas.getContext('2d');
  if (!ctx) return;
  
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;
  
  const particleCount = 100;
  const particleArray: Array<{
    x: number;
    y: number;
    size: number;
    speedX: number;
    speedY: number;
    opacity: number;
  }> = [];
  
  for (let i = 0; i < particleCount; i++) {
    particleArray.push({
      x: Math.random() * canvas.width,
      y: Math.random() * canvas.height,
      size: Math.random() * 2 + 1,
      speedX: (Math.random() - 0.5) * 0.5,
      speedY: (Math.random() - 0.5) * 0.5,
      opacity: Math.random() * 0.5 + 0.2
    });
  }
  
  const animateParticles = () => {
    if (!canvas || !ctx) return;
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    particleArray.forEach(particle => {
      particle.x += particle.speedX;
      particle.y += particle.speedY;
      
      if (particle.x < 0) particle.x = canvas.width;
      if (particle.x > canvas.width) particle.x = 0;
      if (particle.y < 0) particle.y = canvas.height;
      if (particle.y > canvas.height) particle.y = 0;
      
      // Draw with brand colors
      const colorChoice = Math.random() > 0.5 ? 'rgba(53, 167, 255, ' : 'rgba(56, 97, 140, ';
      ctx.fillStyle = colorChoice + particle.opacity + ')';
      ctx.beginPath();
      ctx.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2);
      ctx.fill();
    });
    
    requestAnimationFrame(animateParticles);
  };
  
  animateParticles();
};

onMounted(() => {
  if (canvasWrapper.value) {
    initThree();
  }
  if (particlesCanvas.value) {
    initParticlesCanvas();
  }
  window.addEventListener('resize', handleResize);
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleResize);
  
  if (animationFrameId) {
    cancelAnimationFrame(animationFrameId);
  }
  
  if (controls) {
    controls.dispose();
  }
  
  if (renderer) {
    renderer.dispose();
    if (renderer.domElement.parentNode) {
      renderer.domElement.parentNode.removeChild(renderer.domElement);
    }
  }
  
  // Dispose geometries and materials
  if (boxMesh) {
    (boxMesh.geometry as THREE.BufferGeometry).dispose();
    const mat = boxMesh.material as THREE.MeshStandardMaterial;
    mat.map?.dispose();
    mat.dispose();
  }
  
  if (particles) {
    (particles.geometry as THREE.BufferGeometry).dispose();
    (particles.material as THREE.Material).dispose();
  }
  
  scene = null;
  camera = null;
  renderer = null;
});
</script>

<style scoped>
/* Smooth transitions for all elements */
* {
  will-change: transform, opacity;
}

/* Animated gradient border for button */
@keyframes gradient-border {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

/* Pulsing glow animation */
@keyframes pulse-glow {
  0%, 100% {
    opacity: 0.5;
    transform: scale(1);
  }
  50% {
    opacity: 0.8;
    transform: scale(1.1);
  }
}
</style>