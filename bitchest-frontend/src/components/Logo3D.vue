<template>
  <div ref="container" class="w-full h-full"></div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';
import * as THREE from 'three';

const container = ref<HTMLElement | null>(null);

let renderer: THREE.WebGLRenderer | null = null;
let scene: THREE.Scene | null = null;
let camera: THREE.PerspectiveCamera | null = null;
let sprite: THREE.Sprite | null = null;
let points: THREE.Points | null = null;
let reqId: number | null = null;

onMounted(() => {
  if (!container.value) return;

  // Renderer
  renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
  renderer.setPixelRatio(window.devicePixelRatio);
  renderer.setSize(container.value.clientWidth, container.value.clientHeight);
  container.value.appendChild(renderer.domElement);

  // Scene + Camera
  scene = new THREE.Scene();
  camera = new THREE.PerspectiveCamera(45, container.value.clientWidth / container.value.clientHeight, 0.1, 100);
  camera.position.set(0, 0, 6);
  scene.add(camera);

  // Load texture for sprite (logo)
  const loader = new THREE.TextureLoader();
  loader.load('/Logo1.png', (texture) => {
    const material = new THREE.SpriteMaterial({
      map: texture,
      transparent: true,
      opacity: 0.85,
      depthWrite: false,
      blending: THREE.AdditiveBlending,
    });
    sprite = new THREE.Sprite(material);
    sprite.scale.set(2.2, 2.2, 1);
    sprite.position.set(0, 0, 0);
    scene!.add(sprite);
  });

  // Particles
  const particleCount = 2000;
  const positions = new Float32Array(particleCount * 3);
  for (let i = 0; i < positions.length; i += 3) {
    positions[i] = (Math.random() - 0.5) * 4;
    positions[i + 1] = (Math.random() - 0.5) * 4;
    positions[i + 2] = (Math.random() - 0.5) * 4;
  }

  const geometry = new THREE.BufferGeometry();
  geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
  const materialPoints = new THREE.PointsMaterial({
    color: new THREE.Color('#88ccff'),
    size: 0.02,
    transparent: true,
    blending: THREE.AdditiveBlending,
    sizeAttenuation: true,
  });

  points = new THREE.Points(geometry, materialPoints);
  scene.add(points);

  // Resize handling
  const handleResize = () => {
    if (!container.value || !camera || !renderer) return;
    const w = container.value.clientWidth;
    const h = container.value.clientHeight;
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
    renderer.setSize(w, h);
  };
  window.addEventListener('resize', handleResize);

  // Animation loop
  const clock = new THREE.Clock();
  const animate = () => {
    const t = clock.getElapsedTime();

    if (sprite) {
      sprite.position.y = Math.sin(t * 0.5) * 0.12;
      (sprite.material as THREE.SpriteMaterial).opacity = 0.85 + Math.sin(t) * 0.15;
      sprite.rotation.z = Math.sin(t * 0.3) * 0.13;
      const hue = 200 + Math.sin(t) * 60;
      (sprite.material as any).color = new THREE.Color(`hsl(${hue}, 100%, ${85 + Math.sin(t * 2) * 15}%)`);
    }

    if (points) {
      points.rotation.x = t * 0.1;
      points.rotation.y = t * 0.15;
      const positionsAttr = points.geometry.attributes.position as THREE.BufferAttribute;
      const arr = positionsAttr.array as Float32Array;
      for (let i = 0; i < arr.length; i += 3) {
        const x = arr[i];
        const y = arr[i + 1];
        const z = arr[i + 2];
        arr[i] = x + Math.sin(t + y) * 0.01;
        arr[i + 1] = y + Math.cos(t + x) * 0.01;
        arr[i + 2] = z + Math.sin(t + z) * 0.01;
      }
      positionsAttr.needsUpdate = true;
    }

    renderer!.render(scene!, camera!);
    reqId = requestAnimationFrame(animate);
  };

  animate();

  onBeforeUnmount(() => {
    if (reqId) cancelAnimationFrame(reqId);
    window.removeEventListener('resize', handleResize);
    if (renderer) {
      renderer.dispose();
      renderer.forceContextLoss();
      if (renderer.domElement && renderer.domElement.parentNode) {
        renderer.domElement.parentNode.removeChild(renderer.domElement);
      }
    }
    // Dispose scene objects
    if (points) {
      points.geometry.dispose();
      (points.material as THREE.Material).dispose();
    }
    if (sprite) {
      (sprite.material as THREE.Material).dispose();
      if ((sprite.material as any).map) (sprite.material as any).map.dispose();
    }
    scene = null;
    camera = null;
    renderer = null;
  });
});
</script>

<style scoped>
/* Ensure container has a size, adjust as needed where you use the component */
div {
  width: 100%;
  height: 100%;
}
</style>