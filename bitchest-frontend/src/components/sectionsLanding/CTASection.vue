<template>
  <div>
    <div v-if="MotionAvailable">
      <Motion tag="div"
        class="relative text-white py-16 md:py-24 px-4 md:px-6 overflow-hidden"
        :style="{ background: 'linear-gradient(135deg, #0a0f1a 0%, #1a2332 50%, #0a0f1a 100%)' }"
        :initial="sectionInitial"
        :while-in-view="sectionAnimate"
        :viewport="{ once: true, margin: '-100px' }"
      >
        <!-- Animated particles canvas background -->
        <canvas ref="particlesCanvas" class="absolute inset-0 w-full h-full pointer-events-none"></canvas>

        <!-- Holographic grid with brand colors -->
        <div class="absolute inset-0 opacity-[0.08] pointer-events-none z-0">
          <div
            class="absolute inset-0"
            :style="{
              backgroundImage: `linear-gradient(to right, rgba(53, 167, 255, 0.15) 1px, transparent 1px), linear-gradient(to bottom, rgba(53, 167, 255, 0.15) 1px, transparent 1px)`,
              backgroundSize: '80px 80px',
              transform: 'perspective(1000px) rotateX(60deg)'
            }"
          />
        </div>

        <!-- Background orbs with brand colors -->
        <div class="absolute inset-0 pointer-events-none">
          <div class="absolute top-1/4 left-1/4 w-96 h-96 rounded-full blur-3xl opacity-[0.08]" :style="{ backgroundColor: 'var(--blue-dark)' }"></div>
          <div class="absolute bottom-1/3 right-1/4 w-80 h-80 rounded-full blur-3xl opacity-[0.08]" :style="{ backgroundColor: 'var(--blue)' }"></div>
        </div>
        <Motion tag="div" class="relative z-10 max-w-6xl mx-auto grid lg:grid-cols-2 gap-8 lg:gap-16" :variants="staggerContainer">
          <!-- Left column -->
          <Motion tag="div" class="flex flex-col justify-center" :variants="fadeInUp">
            <Motion tag="h2"
              class="text-3xl md:text-5xl font-bold mb-6 text-app-secondary leading-tight"
              :variants="fadeInUp"
            >
              Prêt à transformer votre expérience crypto?
            </Motion>

            <Motion tag="p" class="text-lg text-slate-300 mb-8" :variants="fadeInUp">
              Rejoignez des millions d'utilisateurs dès aujourd'hui.
              <Motion tag="span" class="block mt-2 text-app-secondary font-semibold" :while-hover="{ scale: 1.05 }">
                Aucun dépôt minimum requis.
              </Motion>
            </Motion>

            <Motion tag="div" :while-hover="{ scale: 1.05 }" :while-tap="{ scale: 0.95 }">
              <RouterLink
                data-cta-button
                to="/signup"
                class="inline-flex items-center text-white px-8 py-4 rounded-xl font-semibold transition-all hover:shadow-xl hover:scale-105 relative overflow-hidden group"
                :style="primaryButtonStyle"
                @mouseenter="primaryHover(true)"
                @mouseleave="primaryHover(false)"
              >
                Créer un compte gratuit
                <Motion tag="div" :animate="{ x: [0, 5, 0] }" :transition="{ repeat: Infinity, duration: 1.5 }">
                  <ArrowRight class="ml-2 h-5 w-5" />
                </Motion>

                <div class="absolute inset-0 rounded-xl bg-white opacity-0 group-hover:opacity-10 transition-opacity duration-300 pointer-events-none"></div>
                <!-- Shine effect -->
                <div class="absolute inset-0 -translate-x-full group-hover:translate-x-full transition-transform duration-700 bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>
              </RouterLink>
            </Motion>

            <Motion tag="div" class="flex flex-wrap gap-6 mt-10" :variants="staggerContainer">
              <Motion tag="div"
                v-for="(item, i) in leftBullets"
                :key="i"
                class="flex items-center space-x-2 text-slate-400 hover:text-white transition-colors"
                :variants="fadeInUp"
                :while-hover="{ scale: 1.1 }"
              >
                <Motion tag="div"
                  class="text-app-secondary"
                  :animate="{ rotate: [0, 15, -15, 0] }"
                  :transition="{ duration: 2, repeat: Infinity }"
                >
                  <component :is="item.icon" class="h-5 w-5" />
                </Motion>
                <span>{{ item.text }}</span>
              </Motion>
            </Motion>
          </Motion>

          <!-- Right column -->
          <Motion tag="div" class="flex flex-col justify-center" :variants="fadeInUp">
            <Motion tag="h3" class="text-3xl font-bold text-app-secondary mb-6" :variants="fadeInUp">
              Excellence reconnue
            </Motion>

            <Motion tag="p" class="text-lg text-slate-400 mb-8" :variants="fadeInUp">
              Établir de nouveaux standards dans le trading de cryptomonnaies
            </Motion>

            <Motion tag="div" class="grid sm:grid-cols-2 gap-6" :variants="staggerContainer">
              <Motion tag="div"
                v-for="(award, i) in awards"
                :key="i"
                class="bg-slate-800/50 backdrop-blur-sm p-6 rounded-xl border border-slate-700 transition-all hover:shadow-lg"
                :style="awardCardStyle"
                @mouseenter="awardHover($event, true)"
                @mouseleave="awardHover($event, false)"
                :variants="fadeInUp"
                :while-hover="{ y: -5 }"
              >
                <Motion tag="div" class="mb-4" :animate="{ rotate: [0, 10, -10, 0] }" :transition="{ duration: 5, repeat: Infinity }">
                  <component :is="award.iconComponent" class="h-8 w-8" v-if="award.iconComponent" />
                  <div v-else v-html="award.iconHtml"></div>
                </Motion>

                <h4 class="text-white font-semibold mb-2">{{ award.title }}</h4>
                <p class="text-slate-400 text-sm">{{ award.subtitle }}</p>

                <Motion tag="button" class="mt-4 flex items-center text-app-secondary hover:text-white transition-colors group" :while-hover="{ x: 5 }">
                  En savoir plus
                  <ChevronRight class="ml-1 h-4 w-4 group-hover:translate-x-1 transition-transform" />
                </Motion>
              </Motion>
            </Motion>
          </Motion>
        </Motion>
      </Motion>
    </div>

    <div v-else class="bg-slate-900 text-white py-12 px-4 rounded-lg">
      <div class="max-w-4xl mx-auto text-center">
        <h2 class="text-2xl font-bold text-app-secondary mb-3">Prêt à transformer votre expérience crypto?</h2>
        <p class="text-slate-300 mb-4">Rejoignez des millions d'utilisateurs dès aujourd'hui. Aucun dépôt minimum requis.</p>
        <RouterLink to="/signup" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold">Créer un compte gratuit</RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Motion } from '@motionone/vue';
import { RouterLink } from 'vue-router';
import { ArrowRight, Shield, Zap, Users, Star, TrendingUp, ChevronRight } from 'lucide-vue-next';
import { useAnimatedBackground } from '../../composables/useAnimatedBackground';

const { particlesCanvas } = useAnimatedBackground();

/* Motion presets (mirrors framer-motion setup) */
const fadeInUp = {
  initial: { y: 60, opacity: 0 },
  animate: { y: 0, opacity: 1 },
  transition: { duration: 0.6, ease: 'easeOut' }
};
const staggerContainer = { animate: { transition: { staggerChildren: 0.1 } } };

const sectionInitial = { opacity: 0 };
const sectionAnimate = { opacity: 1 };

/* Left column bullets */
const leftBullets = [
  { icon: Shield, text: 'Sécurité bancaire' },
  { icon: Zap, text: 'Transactions instantanées' },
  { icon: Users, text: 'Support 24/7' }
];

/* Awards on right column - mix of components and small HTML */
const awards = [
  { iconComponent: Star, title: 'Meilleure plateforme crypto 2024', subtitle: 'FinTech Awards' },
  { iconComponent: null, iconHtml: '<div class="text-2xl font-bold PnL--pos">4.9</div>', title: 'Note Trustpilot', subtitle: '10 000+ avis' },
  { iconComponent: TrendingUp, title: 'Recommandé par', subtitle: 'Forbes, Bloomberg, TechCrunch' }
];

/* Styles + hover state */
const primaryHovered = ref(false);
const primaryButtonStyle = {
  background: 'linear-gradient(to right, var(--blue), var(--blue-dark))',
  boxShadow: '0 0 30px rgba(53, 167, 255, 0.3), 0 0 20px rgba(56, 97, 140, 0.25)'
};

function primaryHover(enter = true) {
  primaryHovered.value = enter;
}

/* Award card hover helpers to mimic inline behavior */
const awardCardStyle = { '--hover-border': 'var(--blue)' } as Record<string, any>;
function awardHover(e: Event, enter: boolean) {
  const el = e.currentTarget as HTMLElement;
  if (enter) {
    el.style.borderColor = 'var(--blue)';
    el.style.boxShadow = '0 0 20px rgba(53, 167, 255, 0.1)';
  } else {
    el.style.borderColor = '';
    el.style.boxShadow = '';
  }
}

// Particles animation is handled by useAnimatedBackground composable

// new: drapeau indiquant si Motion est disponible au runtime
const MotionAvailable = typeof Motion !== 'undefined' && Motion !== null;
</script>

<style scoped>
/* No additional styles - everything uses Tailwind utilities and inline styles */
</style>