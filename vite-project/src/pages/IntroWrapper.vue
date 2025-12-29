<template>
  <transition name="fade" appear>
    <div class="fixed inset-0 z-50" v-if="visible">
      <Suspense>
        <template #default>
          <div class="relative min-h-screen w-full overflow-hidden bg-black">
            <CryptoIntro3D @end="handleEnd" />
          </div>
        </template>
        <template #fallback>
          <LoadingFallback />
        </template>
      </Suspense>
    </div>
  </transition>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';
import CryptoIntro3D from './CryptoIntro3D.vue';

const emit = defineEmits<{
  end: [];
}>();

const visible = ref(true);

onMounted(() => {
  // Prevent scrolling while intro is visible
  document.body.style.overflow = 'hidden';
});

onBeforeUnmount(() => {
  document.body.style.overflow = '';
});

const handleEnd = () => {
  // Allow a smooth exit animation then hide
  visible.value = false;
  // Emit end event to parent (App.vue)
  emit('end');
};
</script>

<script lang="ts">
// LoadingFallback as separate exported component (keeps file tidy)
import { defineComponent, h } from 'vue';
import { motion } from '@motionone/vue'; // optional - if not present, basic CSS animation is used

export const LoadingFallback = defineComponent({
  name: 'LoadingFallback',
  setup() {
    return () => h('div', { class: 'fixed inset-0 bg-black flex items-center justify-center' }, [
      h('div', { class: 'text-white text-2xl animate-pulse' }, 'Chargement...')
    ]);
  }
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.6s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>