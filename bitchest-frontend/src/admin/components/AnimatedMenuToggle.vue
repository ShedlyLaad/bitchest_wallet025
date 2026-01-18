<template>
  <button
    @click="$emit('toggle')"
    aria-label="Toggle menu"
    class="focus:outline-none z-50 p-2.5 rounded-lg hover:bg-gray-700/50 active:bg-gray-700/70 transition-colors touch-manipulation shadow-lg bg-gray-800/90 backdrop-blur-sm border border-gray-700/50"
  >
    <div class="relative w-5 h-5">
      <!-- Top Line -->
      <span
        class="absolute top-0 left-0 w-full h-0.5 bg-gray-300 rounded-full transition-all duration-300"
        :class="isOpen ? 'top-2 rotate-45' : ''"
      ></span>
      <!-- Middle Line -->
      <span
        class="absolute top-2 left-0 w-full h-0.5 bg-gray-300 rounded-full transition-all duration-200"
        :class="isOpen ? 'opacity-0' : 'opacity-100'"
      ></span>
      <!-- Bottom Line -->
      <span
        class="absolute top-4 left-0 w-full h-0.5 bg-gray-300 rounded-full transition-all duration-300"
        :class="isOpen ? 'top-2 -rotate-45' : ''"
      ></span>
    </div>
  </button>
</template>

<script setup lang="ts">
defineProps<{
  isOpen: boolean;
}>();

defineEmits<{
  toggle: [];
}>();
</script>

<style scoped>
/* Smooth transitions handled by Tailwind classes */
</style>

