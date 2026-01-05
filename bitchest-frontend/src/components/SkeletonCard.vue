<template>
  <div class="skeleton-card bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl p-6 border border-gray-700">
    <div class="flex items-center justify-between mb-4">
      <SkeletonLoader variant="circular" :width="'48px'" :height="'48px'" />
      <SkeletonLoader variant="rectangular" :width="'80px'" :height="'24px'" />
    </div>
    <div class="space-y-2">
      <SkeletonLoader variant="text" size="large" :width="'60%'" />
      <SkeletonLoader variant="text" size="small" :width="'40%'" />
    </div>
  </div>
</template>

<script setup lang="ts">
import SkeletonLoader from './SkeletonLoader.vue';
</script>

<style scoped>
.skeleton-card {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.8;
  }
}
</style>

