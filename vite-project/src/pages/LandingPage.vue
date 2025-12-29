<template>
  <div class="min-h-screen bg-gray-900 text-white overflow-hidden">
    <!-- Scroll to Top Button -->
    <Motion
      tag="button"
      @click="scrollToTop"
      class="fixed bottom-8 right-8 z-50 p-3 rounded-full shadow-lg transition-all duration-300 backdrop-blur-sm"
      :style="scrollTopButtonStyle"
      :initial="scrollButtonInitial"
      :animate="scrollButtonAnimate"
      :transition="scrollButtonTransition"
      :while-hover="{ scale: 1.1 }"
      :while-tap="{ scale: 0.9 }"
      @mouseenter="hoverScrollButton(true, $event)"
      @mouseleave="hoverScrollButton(false, $event)"
    >
      <MoveUp class="h-6 w-6 text-white" />
    </Motion>

    <!-- Progress Bar -->
    <Motion
      class="fixed top-0 left-0 right-0 h-1 origin-left z-50"
      :style="progressBarStyle"
      :initial="{ scaleX: 0 }"
      :animate="{ scaleX: scrollProgress }"
      :transition="{ duration: 0.2, ease: 'easeOut' }"
    />

    <!-- Sections -->
    <HeroSection />
    <PartnersSection />
    <StatsSection />
    <FeaturesSection />
    <TestimonialsSection />
    <!-- Team intentionally commented out like original -->
    <!-- <TeamSection /> -->
    <CTASection />
    <FooterSection />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { MoveUp } from 'lucide-vue-next';
import { Motion } from '@motionone/vue';

import HeroSection from '../components/sectionsLanding/HeroSection.vue';
import PartnersSection from '../components/sectionsLanding/PartnersSection.vue';
import StatsSection from '../components/sectionsLanding/StatsSection.vue';
import FeaturesSection from '../components/sectionsLanding/FeaturesSection.vue';
import TestimonialsSection from '../components/sectionsLanding/TestimonialsSection.vue';
import TeamSection from '../components/sectionsLanding/TeamSection.vue';
import CTASection from '../components/sectionsLanding/CTASection.vue';
import FooterSection from '../components/sectionsLanding/FooterSection.vue';

const showScrollTop = ref(false);
const scrollProgress = ref(0);

const updateScroll = () => {
  const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
  const docHeight =
    document.documentElement.scrollHeight - document.documentElement.clientHeight;
  showScrollTop.value = scrollTop > 400;
  scrollProgress.value = docHeight > 0 ? Math.min(1, scrollTop / docHeight) : 0;
};

onMounted(() => {
  updateScroll();
  window.addEventListener('scroll', updateScroll, { passive: true });
});

onBeforeUnmount(() => {
  window.removeEventListener('scroll', updateScroll);
});

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const scrollTopButtonStyle = {
  backgroundColor: 'var(--blue-dark)',
  borderColor: 'var(--blue)',
  borderWidth: '1px',
  borderStyle: 'solid',
  boxShadow: '0 0 20px rgba(53, 167, 255, 0.3)'
};

const progressBarStyle = {
  background: 'linear-gradient(90deg, var(--blue-dark), var(--blue))'
};

const scrollButtonInitial = { scale: 0, opacity: 0 };
const scrollButtonTransition = { duration: 0.3, ease: 'easeInOut' };
const scrollButtonAnimate = computed(() =>
  showScrollTop.value ? { scale: 1, opacity: 1 } : { scale: 0, opacity: 0 }
);

function hoverScrollButton(enter: boolean, event?: Event) {
  const el = event?.currentTarget as HTMLElement | undefined;
  if (!el) return;
  if (enter) {
    el.style.backgroundColor = 'var(--blue)';
    el.style.boxShadow = '0 0 30px rgba(53, 167, 255, 0.4)';
  } else {
    el.style.backgroundColor = 'var(--blue-dark)';
    el.style.boxShadow = '0 0 20px rgba(53, 167, 255, 0.3)';
  }
}
</script>

<style scoped>
/* nothing additional for now; UI handled by Tailwind + inline styles */
</style>