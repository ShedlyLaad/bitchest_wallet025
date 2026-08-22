<template>
  <div class="absolute inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
    <!-- Base gradient -->
    <div class="absolute inset-0" :style="{ background: 'linear-gradient(135deg, #0a0f1a 0%, #131b29 50%, #0a0f1a 100%)' }" />

    <!-- Subtle particles -->
    <canvas v-if="particles" ref="particlesCanvas" class="absolute inset-0 w-full h-full opacity-60"></canvas>

    <!-- Faint grid, low opacity so it reads as texture, not a light show -->
    <div class="absolute inset-0 opacity-[0.035]">
      <div
        class="absolute inset-0"
        :style="{
          backgroundImage: `linear-gradient(to right, rgba(53, 167, 255, 0.15) 1px, transparent 1px), linear-gradient(to bottom, rgba(53, 167, 255, 0.15) 1px, transparent 1px)`,
          backgroundSize: '64px 64px'
        }"
      />
    </div>

    <!-- One or two soft brand-color glows, kept subtle -->
    <div
      v-for="(orb, i) in orbs"
      :key="i"
      class="absolute rounded-full blur-3xl"
      :style="{
        width: orb.size,
        height: orb.size,
        top: orb.top,
        left: orb.left,
        right: orb.right,
        bottom: orb.bottom,
        backgroundColor: orb.color,
        opacity: 0.07
      }"
    />
  </div>
</template>

<script setup lang="ts">
import { useAnimatedBackground } from '../../composables/useAnimatedBackground';

interface Orb {
  size: string;
  color: string;
  top?: string;
  left?: string;
  right?: string;
  bottom?: string;
}

interface Props {
  particles?: boolean;
  orbs?: Orb[];
}

withDefaults(defineProps<Props>(), {
  particles: true,
  orbs: () => [
    { size: '480px', color: 'var(--blue-dark)', top: '10%', left: '-10%' },
    { size: '420px', color: 'var(--blue)', bottom: '10%', right: '-8%' }
  ]
});

const { particlesCanvas } = useAnimatedBackground();
</script>
