<template>
  <div ref="root" class="fixed inset-0 z-50 flex items-center justify-center overflow-hidden bg-[#020d1f]">
    <!-- gradient background animated via CSS -->
    <div class="absolute inset-0 z-0 bg-gradient-animated" />
    <!-- Three.js canvas will be injected -->
    <div ref="canvasWrapper" class="absolute inset-0 z-10"></div>

    <!-- Main Content Card -->
    <div
      class="relative z-30 w-[90vw] max-w-[600px] rounded-2xl backdrop-blur-xl bg-[#0a1f3c]/30 border border-blue-500/30 p-8 shadow-[0_0_50px_rgba(0,102,255,0.2)] overflow-hidden"
      :style="{ transform: `translateY(${yOffset}px)`, opacity: cardOpacity }"
    >
      <h1 class="font-['Orbitron'] text-5xl md:text-6xl font-bold text-center tracking-wider bg-gradient-to-r from-blue-300 via-blue-400 to-white bg-clip-text text-transparent mb-4">
        Bitchest
      </h1>
      <p class="font-['Inter'] text-center text-blue-200/80 mb-8 text-lg md:text-xl">
        The Future of Digital Transactions
      </p>

      <button
        class="w-full py-4 px-8 bg-gradient-to-r from-blue-600/20 to-blue-400/20 rounded-xl border border-blue-400/30 text-white font-['Poppins'] text-lg transition-all hover:scale-105 hover:shadow-[0_0_30px_rgba(0,102,255,0.3)]"
        @click="onExplore"
      >
        Explore the Future
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';
import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls';

const emit = defineEmits<{
  end: [];
}>();

const root = ref<HTMLElement | null>(null);
const canvasWrapper = ref<HTMLElement | null>(null);

const yOffset = ref(0);
const cardOpacity = ref(0);

let renderer: THREE.WebGLRenderer | null = null;
let scene: THREE.Scene | null = null;
let camera: THREE.PerspectiveCamera | null = null;
let sphere: THREE.Mesh | null = null;
let controls: OrbitControls | null = null;
let rafId: number | null = null;
let startTime = 0;
let resizeHandler: (() => void) | null = null;
let autoEndTimeout: ReturnType<typeof setTimeout> | null = null;

const initThree = () => {
  if (!canvasWrapper.value) return;

  // Renderer
  renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
  renderer.setPixelRatio(window.devicePixelRatio || 1);
  renderer.setSize(canvasWrapper.value.clientWidth, canvasWrapper.value.clientHeight);
  renderer.setClearColor(0x000000, 0); // transparent
  canvasWrapper.value.appendChild(renderer.domElement);

  // Scene & Camera
  scene = new THREE.Scene();
  camera = new THREE.PerspectiveCamera(75, canvasWrapper.value.clientWidth / canvasWrapper.value.clientHeight, 0.1, 100);
  camera.position.set(0, 0, 4);

  // Lights
  const ambient = new THREE.AmbientLight(0xffffff, 0.08);
  scene.add(ambient);
  const dir = new THREE.DirectionalLight(0x0066ff, 1.0);
  dir.position.set(10, 10, 5);
  scene.add(dir);
  const point = new THREE.PointLight(0xffffff, 0.5);
  point.position.set(-10, -10, -5);
  scene.add(point);

  // Sphere geometry + material (simple animated distortion effect via vertex displacement on CPU)
  const geometry = new THREE.SphereGeometry(1, 128, 128);
  const material = new THREE.MeshStandardMaterial({
    color: 0x0066ff,
    emissive: 0x002244,
    metalness: 0.6,
    roughness: 0.35,
    transparent: true,
    opacity: 0.95
  });

  sphere = new THREE.Mesh(geometry, material);
  scene.add(sphere);

  // Save initial positions for CPU-based vertex wobble
  const positionAttr = geometry.attributes.position as THREE.BufferAttribute;
  const initialPositions = new Float32Array(positionAttr.array.length);
  initialPositions.set(positionAttr.array);

  // OrbitControls
  controls = new OrbitControls(camera, renderer.domElement);
  controls.enableZoom = false;
  controls.autoRotate = true;
  controls.autoRotateSpeed = 0.5;
  controls.enablePan = false;

  // Resize handling
  resizeHandler = () => {
    if (!canvasWrapper.value || !camera || !renderer) return;
    const w = canvasWrapper.value.clientWidth;
    const h = canvasWrapper.value.clientHeight;
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
    renderer.setSize(w, h);
  };
  window.addEventListener('resize', resizeHandler);

  // Animation loop
  startTime = performance.now();
  const animate = (t: number) => {
    const elapsed = (t - startTime) / 1000;

    // subtle card entrance animation values
    cardOpacity.value = Math.min(1, elapsed / 1.2);
    yOffset.value = Math.max(0, 10 * (1 - Math.min(1, elapsed / 1.2)));

    // sphere rotation
    if (sphere) {
      sphere.rotation.y = elapsed * 0.25;
      sphere.rotation.x = Math.sin(elapsed * 0.3) * 0.08;

      // simple CPU vertex displacement for organic movement
      const pos = (sphere.geometry as THREE.BufferGeometry).attributes.position as THREE.BufferAttribute;
      for (let i = 0; i < pos.count; i++) {
        const ix = i * 3;
        const iy = ix + 1;
        const iz = ix + 2;

        const nx = initialPositions[ix];
        const ny = initialPositions[iy];
        const nz = initialPositions[iz];

        // distance from center as factor
        const r = Math.sqrt(nx * nx + ny * ny + nz * nz);
        const wobble = Math.sin(elapsed * 1.5 + r * 4.0) * 0.02; // small displacement
        pos.array[ix] = nx + (nx / r) * wobble;
        pos.array[iy] = ny + (ny / r) * wobble;
        pos.array[iz] = nz + (nz / r) * wobble;
      }
      pos.needsUpdate = true;
    }

    controls?.update();
    renderer?.render(scene!, camera!);
    rafId = requestAnimationFrame(animate);
  };

  rafId = requestAnimationFrame(animate);

  // call onEnd after the same timing logic as original (8s + 2.5s)
  autoEndTimeout = setTimeout(() => {
    setTimeout(() => {
      emit('end');
    }, 2500);
  }, 8000);
};

onMounted(() => {
  initThree();
});

// Cleanup function
const cleanup = () => {
  if (resizeHandler) {
    window.removeEventListener('resize', resizeHandler);
    resizeHandler = null;
  }
  if (rafId) {
    cancelAnimationFrame(rafId);
    rafId = null;
  }
  if (autoEndTimeout) {
    clearTimeout(autoEndTimeout);
    autoEndTimeout = null;
  }
  controls?.dispose();
  controls = null;
  if (sphere) {
    (sphere.geometry as THREE.BufferGeometry).dispose();
    (sphere.material as THREE.Material).dispose();
    scene?.remove(sphere);
    sphere = null;
  }
  if (renderer) {
    renderer.dispose();
    if (renderer.domElement && renderer.domElement.parentNode) {
      renderer.domElement.parentNode.removeChild(renderer.domElement);
    }
    renderer = null;
  }
  scene = null;
  camera = null;
};

onBeforeUnmount(() => {
  cleanup();
});

// simple CTA handler (user clicked "Explore the Future")
const onExplore = () => {
  // Clean up before emitting end event
  cleanup();
  // emit end event immediately to close the intro
  emit('end');
};
</script>

<style scoped>
.bg-gradient-animated {
  animation: bgShift 20s ease-in-out infinite alternate;
  background: radial-gradient(circle at 20% 30%, #0a1f3c, #020d1f, #0a1f3c);
}

@keyframes bgShift {
  0% {
    background: radial-gradient(circle at 20% 30%, #0a1f3c, #020d1f, #0a1f3c);
  }
  100% {
    background: radial-gradient(circle at 80% 70%, #0a1f3c, #020d1f, #0a1f3c);
  }
}
</style>