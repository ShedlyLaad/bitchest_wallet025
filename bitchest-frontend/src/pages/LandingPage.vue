<template>
  <div class="min-h-screen bg-gray-900 text-white overflow-hidden relative">
    <!-- Base background: each section layers its own subtle depth via SectionBackground -->
    <div class="fixed inset-0 pointer-events-none z-0 bg-gradient-to-br from-gray-900 via-gray-900 to-gray-950"></div>

    <!-- Scroll to Top Button -->
    <Motion
      tag="button"
      @click="scrollToTop"
      class="fixed bottom-8 right-8 z-50 p-4 rounded-full shadow-2xl transition-all duration-300 backdrop-blur-md border-2 group"
      :style="scrollTopButtonStyle"
      :initial="scrollButtonInitial"
      :animate="scrollButtonAnimate"
      :transition="scrollButtonTransition"
      :while-hover="{ scale: 1.15, y: -5 }"
      :while-tap="{ scale: 0.9 }"
      @mouseenter="hoverScrollButton(true, $event)"
      @mouseleave="hoverScrollButton(false, $event)"
      aria-label="Scroll to top"
    >
      <MoveUp class="h-6 w-6 text-white transition-transform group-hover:-translate-y-1" />
      <div class="absolute inset-0 rounded-full opacity-0 group-hover:opacity-100 transition-opacity" 
           :style="{ background: 'radial-gradient(circle, var(--blue), transparent)' }"></div>
    </Motion>

    <!-- Progress Bar with gradient -->
    <Motion
      class="fixed top-0 left-0 right-0 h-1.5 origin-left z-50 shadow-lg"
      :style="progressBarStyle"
      :initial="{ scaleX: 0 }"
      :animate="{ scaleX: scrollProgress }"
      :transition="{ duration: 0.3, ease: 'easeOut' }"
    >
      <div class="h-full w-full" :style="progressBarGradient"></div>
    </Motion>

    <!-- Sections with smooth transitions -->
    <div class="relative z-10">
      <HeroSection />
      <PartnersSection />
      <FeaturesSection />
      <!-- Team intentionally commented out like original -->
      <!-- <TeamSection /> -->
      <CTASection />
      <FooterSection />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { MoveUp } from 'lucide-vue-next';
import { Motion } from '@motionone/vue';

import HeroSection from '../components/sectionsLanding/HeroSection.vue';
import PartnersSection from '../components/sectionsLanding/PartnersSection.vue';
import FeaturesSection from '../components/sectionsLanding/FeaturesSection.vue';
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
  borderWidth: '2px',
  borderStyle: 'solid',
  boxShadow: '0 8px 32px rgba(53, 167, 255, 0.4), 0 0 20px rgba(56, 97, 140, 0.3)'
};

const progressBarStyle = {
  background: 'transparent'
};

const progressBarGradient = {
  background: 'linear-gradient(90deg, var(--accent-green), var(--blue), var(--blue-dark), var(--accent-green))',
  backgroundSize: '200% 100%',
  animation: 'gradient-shift 3s ease infinite'
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
    el.style.borderColor = 'var(--accent-green)';
    el.style.boxShadow = '0 0 40px rgba(53, 167, 255, 0.6), 0 0 20px rgba(1, 255, 25, 0.3)';
  } else {
    el.style.backgroundColor = 'var(--blue-dark)';
    el.style.borderColor = 'var(--blue)';
    el.style.boxShadow = '0 8px 32px rgba(53, 167, 255, 0.4), 0 0 20px rgba(56, 97, 140, 0.3)';
  }
}
</script>

<style scoped>
/* Animated gradient for progress bar */
@keyframes gradient-shift {
  0%, 100% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
}

/* Smooth scroll behavior */
html {
  scroll-behavior: smooth;
}

/* Enhanced scroll button glow */
@keyframes button-glow {
  0%, 100% {
    box-shadow: 0 8px 32px rgba(53, 167, 255, 0.4), 0 0 20px rgba(56, 97, 140, 0.3);
  }
  50% {
    box-shadow: 0 8px 40px rgba(53, 167, 255, 0.6), 0 0 30px rgba(1, 255, 25, 0.4);
  }
}

/* Section transitions */
section {
  position: relative;
  transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}

/* Delay classes for staggered animations */
.delay-1000 {
  animation-delay: 1s;
}

.delay-2000 {
  animation-delay: 2s;
}
</style>