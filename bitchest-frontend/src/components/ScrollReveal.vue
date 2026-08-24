<template>
  <div ref="container" :style="{ width: widthValue, overflow: 'hidden' }">
    <div
      ref="content"
      :class="['transition-transform transition-opacity duration-700 ease-[cubic-bezier(0.33,1,0.68,1)]', inView ? 'translate-y-0 opacity-100' : 'translate-y-12 opacity-0']"
      :style="{ willChange: 'transform, opacity' }"
    >
      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, computed, nextTick } from 'vue';

interface Props {
  width?: 'fit-content' | '100%';
}
const props = defineProps<Props>();
const width = props.width ?? 'fit-content';
const widthValue = computed(() => (width === '100%' ? '100%' : 'fit-content'));

const container = ref<HTMLElement | null>(null);
const content = ref<HTMLElement | null>(null);
const inView = ref(false);
let observer: IntersectionObserver | null = null;

onMounted(async () => {
  await nextTick();
  if (!container.value) return;

  observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          inView.value = true;
          if (observer) {
            observer.unobserve(entry.target);
          }
        }
      });
    },
    {
      root: null,
      rootMargin: '-100px',
      threshold: 0.01,
    }
  );

  if (container.value) observer.observe(container.value);
});

onBeforeUnmount(() => {
  if (observer && container.value) observer.unobserve(container.value);
  observer = null;
});
</script>

<style scoped>
/* La transition utilise classes utilitaires + petites classes locales si besoin */
.translate-y-12 {
  transform: translateY(3rem); /* correspond à 48px ~ proche de 75px en design original; ajustable */
}
.translate-y-0 {
  transform: translateY(0);
}
.opacity-0 {
  opacity: 0;
}
.opacity-100 {
  opacity: 1;
}
</style>