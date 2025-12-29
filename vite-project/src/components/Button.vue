<template>
  <button
    :type="type"
    :disabled="disabled"
    :aria-label="ariaLabel"
    :class="['px-4 py-2 rounded-lg transition-all duration-300', variantClass, disabled ? 'opacity-50 cursor-not-allowed' : '', className]"
    @click="handleClick"
  >
    <slot />
  </button>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  variant?: 'primary' | 'success' | 'danger' | 'secondary';
  onClick?: () => void;
  className?: string;
  type?: 'button' | 'submit' | 'reset';
  disabled?: boolean;
  ariaLabel?: string;
}

const props = defineProps<Props>();
const emit = defineEmits(['click']);

const {
  variant = 'primary',
  onClick,
  className = '',
  type = 'button',
  disabled = false,
  ariaLabel,
} = props;

const variantClass = computed(() => {
  // Mappez aux classes Tailwind (ou remplacez par votre mapping CSS)
  const map: Record<string, string> = {
    primary: 'bg-blue-600 text-white hover:bg-blue-500',
    success: 'bg-green-500 text-white hover:bg-green-400',
    danger: 'bg-red-600 text-white hover:bg-red-500',
    secondary: 'bg-gray-700 text-white hover:bg-gray-600',
  };
  return map[variant] ?? map.primary;
});

function handleClick(e?: Event) {
  if (disabled) return;
  onClick?.();
  emit('click', e);
}
</script>

<style scoped>
/* Ajoutez styles si vous voulez */
</style>