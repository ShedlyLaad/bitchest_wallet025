<template>
  <span>{{ prefix }}{{ formatted }}{{ suffix }}</span>
</template>

<script setup lang="ts">
import { ref, computed, watch, onBeforeUnmount } from 'vue';

interface Props {
  value: number;
  duration?: number;
  prefix?: string;
  suffix?: string;
  decimals?: number;
}

const props = defineProps<Props>();
const {
  value,
  duration = 2000,
  prefix = '',
  suffix = '',
  decimals = 0,
} = props;

const current = ref(0);
const rafId = ref<number | null>(null);

const formatted = computed(() => current.value.toFixed(decimals));

watch(
  () => [value, duration],
  () => {
    const startTime = performance.now();
    const startValue = current.value;
    const target = value;

    const updateCounter = (time: number) => {
      const elapsed = time - startTime;
      const progress = Math.min(elapsed / (duration ?? 2000), 1);
      const easeOutQuart = 1 - Math.pow(1 - progress, 4);
      const newValue = startValue + (target - startValue) * easeOutQuart;

      current.value = newValue;
      if (progress < 1) {
        rafId.value = requestAnimationFrame(updateCounter);
      } else {
        // Ensure final value is exact
        current.value = target;
        rafId.value = null;
      }
    };

    if (rafId.value) {
      cancelAnimationFrame(rafId.value);
    }
    rafId.value = requestAnimationFrame(updateCounter);
  },
  { immediate: true }
);

onBeforeUnmount(() => {
  if (rafId.value) cancelAnimationFrame(rafId.value);
});
</script>

<style scoped>
/* Légère base, adaptez selon votre thème */
span {
  display: inline-block;
}
</style>