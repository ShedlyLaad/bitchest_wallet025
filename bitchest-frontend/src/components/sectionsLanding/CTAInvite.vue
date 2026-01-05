<template>
  <div>
    <!-- rendu animé si Motion dispo -->
    <div v-if="MotionAvailable">
      <Motion
        class="bg-slate-900 text-white py-16 md:py-24 px-4 md:px-6 overflow-hidden"
        :initial="sectionInitial"
        :while-in-view="sectionAnimate"
        :viewport="{ once: true, margin: '-100px' }"
      >
        <Motion class="max-w-6xl mx-auto grid lg:grid-cols-2 gap-8 lg:gap-16" :variants="staggerContainer">
          <!-- Left column -->
          <Motion class="flex flex-col justify-center" :variants="fadeInUp">
            <Motion tag="h2" class="text-3xl md:text-5xl font-bold mb-6 text-app-secondary leading-tight" :variants="fadeInUp">
              Prêt à transformer votre expérience crypto?
            </Motion>

            <Motion tag="p" class="text-lg text-slate-300 mb-8" :variants="fadeInUp">
              Rejoignez des millions d'utilisateurs dès aujourd'hui.
              <Motion tag="span" class="block mt-2 text-app-secondary font-semibold" :while-hover="{ scale: 1.05 }">
                Aucun dépôt minimum requis.
              </Motion>
            </Motion>

            <Motion :while-hover="{ scale: 1.05 }" :while-tap="{ scale: 0.95 }">
              <RouterLink
                to="/signup"
                class="inline-flex items-center text-white px-8 py-4 rounded-xl font-semibold transition-all hover:shadow-lg"
                :style="primaryButtonStyle"
                @mouseenter="primaryHover(true, $event)"
                @mouseleave="primaryHover(false, $event)"
              >
                Créer un compte gratuit
                <Motion :animate="{ x: [0, 5, 0] }" :transition="{ repeat: Infinity, duration: 1.5 }">
                  <ArrowRight class="ml-2 h-5 w-5" />
                </Motion>
              </RouterLink>
            </Motion>

            <Motion class="flex flex-wrap gap-6 mt-10" :variants="staggerContainer">
              <Motion
                v-for="(item, i) in leftBullets"
                :key="i"
                class="flex items-center space-x-2 text-slate-400 hover:text-white transition-colors"
                :variants="fadeInUp"
                :while-hover="{ scale: 1.1 }"
              >
                <Motion class="text-app-secondary" :animate="{ rotate: [0, 15, -15, 0] }" :transition="{ duration: 2, repeat: Infinity }">
                  <component :is="item.icon" class="h-5 w-5" />
                </Motion>
                <span>{{ item.text }}</span>
              </Motion>
            </Motion>
          </Motion>

          <!-- Right column -->
          <Motion class="flex flex-col justify-center" :variants="fadeInUp">
            <Motion tag="h3" class="text-3xl font-bold text-app-secondary mb-6" :variants="fadeInUp">
              Excellence reconnue
            </Motion>

            <Motion tag="p" class="text-lg text-slate-400 mb-8" :variants="fadeInUp">
              Établir de nouveaux standards dans le trading de cryptomonnaies
            </Motion>

            <Motion class="grid sm:grid-cols-2 gap-6" :variants="staggerContainer">
              <Motion
                v-for="(award, i) in awards"
                :key="i"
                class="bg-slate-800/50 backdrop-blur-sm p-6 rounded-xl border border-slate-700 transition-all hover:shadow-lg"
                :style="awardCardBaseStyle"
                @mouseenter="awardHover($event, true)"
                @mouseleave="awardHover($event, false)"
                :variants="fadeInUp"
                :while-hover="{ y: -5 }"
              >
                <Motion class="mb-4" :animate="{ rotate: [0, 10, -10, 0] }" :transition="{ duration: 5, repeat: Infinity }">
                  <component v-if="award.iconComponent" :is="award.iconComponent" class="h-8 w-8" />
                  <div v-else v-html="award.iconHtml" />
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

    <!-- fallback statique minimal si Motion indisponible -->
    <div v-else class="bg-slate-900 text-white py-12 px-4 rounded-lg">
      <div class="max-w-4xl mx-auto text-center">
        <h2 class="text-2xl font-bold text-app-secondary mb-3">Prêt à transformer votre expérience crypto?</h2>
        <p class="text-slate-300 mb-4">Rejoignez des millions d'utilisateurs dès aujourd'hui. Aucun dépôt minimum requis.</p>
        <RouterLink to="/signup" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold">Créer un compte gratuit</RouterLink>

        <div class="mt-6 grid sm:grid-cols-2 gap-4">
          <div class="bg-slate-800/40 p-4 rounded">Meilleure plateforme crypto 2024 — FinTech Awards</div>
          <div class="bg-slate-800/40 p-4 rounded">Note Trustpilot: 4.9 — 10 000+ avis</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
/**
 * CTASection.vue
 * Converted from React + Framer Motion to Vue 3 using @motionone/vue.
 * Preserves exact structure, style, and animation behavior from the original React version.
 *
 * Usage: place <CTASection /> where needed.
 */

import { ref } from 'vue';
import { Motion } from '@motionone/vue';
import { RouterLink } from 'vue-router';
import { ArrowRight, Shield, Zap, Users, Star, TrendingUp, ChevronRight } from 'lucide-vue-next';

/* Motion presets that mirror the Framer Motion definitions */
const fadeInUp = {
  initial: { y: 60, opacity: 0 },
  animate: { y: 0, opacity: 1 },
  transition: { duration: 0.6, ease: 'easeOut' }
};

const staggerContainer = {
  animate: { transition: { staggerChildren: 0.1 } }
};

const sectionInitial = { opacity: 0 };
const sectionAnimate = { opacity: 1 };

/* Left column bullets: use icon components */
const leftBullets = [
  { icon: Shield, text: 'Sécurité bancaire' },
  { icon: Zap, text: 'Transactions instantanées' },
  { icon: Users, text: 'Support 24/7' }
];

/* Awards on right column - mix of icon components and inline HTML (4.9) */
const awards = [
  { iconComponent: Star, title: 'Meilleure plateforme crypto 2024', subtitle: 'FinTech Awards' },
  { iconComponent: null, iconHtml: '<div class="text-2xl font-bold PnL--pos">4.9</div>', title: 'Note Trustpilot', subtitle: '10 000+ avis' },
  { iconComponent: TrendingUp, title: 'Recommandé par', subtitle: 'Forbes, Bloomberg, TechCrunch' }
];

/* Primary button style and hover control (used to reproduce inline boxShadow changes) */
const primaryHovered = ref(false);
const primaryButtonStyle = {
  background: 'linear-gradient(to right, var(--blue), var(--blue-dark))',
  boxShadow: '0 0 20px rgba(53, 167, 255, 0.25)'
};

function primaryHover(enter: boolean, e?: Event) {
  primaryHovered.value = enter;
  if (!e) return;
  const el = e.currentTarget as HTMLElement;
  if (enter) {
    el.style.boxShadow = '0 0 30px rgba(53, 167, 255, 0.35)';
  } else {
    el.style.boxShadow = '0 0 20px rgba(53, 167, 255, 0.25)';
  }
}

/* Award card base style and hover handler to mimic inline style changes */
const awardCardBaseStyle = {
  '--hover-border': 'var(--blue)'
} as Record<string, any>;

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

// new: drapeau indiquant si Motion est disponible au runtime
const MotionAvailable = typeof Motion !== 'undefined' && Motion !== null;
</script>

<style scoped>
/* No external CSS required beyond Tailwind utilities.
   Add helper animation used in other components (kept consistent). */
@keyframes spin-slow {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>