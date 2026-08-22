<template>
  <section class="relative text-white py-16 md:py-24 px-4 md:px-6 overflow-hidden bg-gray-900">
    <SectionBackground />

    <div v-if="MotionAvailable">
      <Motion
        tag="div"
        class="relative z-10 max-w-6xl mx-auto"
        :initial="sectionInitial"
        :while-in-view="sectionAnimate"
        :viewport="{ once: true, margin: '-100px' }"
      >
        <Motion tag="div" class="grid lg:grid-cols-2 gap-8 lg:gap-16" :variants="staggerContainer">
          <!-- Left column -->
          <Motion tag="div" class="flex flex-col justify-center" :variants="fadeInUp">
            <!-- Live badge -->
            <Motion
              tag="div"
              class="inline-flex items-center gap-2 px-4 py-2 rounded-full border mb-6 w-fit transition-all duration-300"
              :style="{
                backgroundColor: 'rgba(56, 97, 140, 0.15)',
                borderColor: 'var(--blue)',
                boxShadow: '0 0 20px rgba(53, 167, 255, 0.2)'
              }"
              :while-hover="{ scale: 1.05 }"
            >
              <span class="h-2 w-2 rounded-full animate-pulse" :style="{ backgroundColor: 'var(--accent-green)' }"></span>
              <span class="text-sm font-medium" style="color: var(--blue)">BitChest Platform — Live</span>
            </Motion>

            <!-- H2 with gradient-shift -->
            <Motion tag="h2" class="text-3xl md:text-5xl font-bold mb-6 leading-tight" :variants="fadeInUp">
              <span class="block text-white mb-2">Ready to Transform Your</span>
              <span
                class="block bg-clip-text text-transparent"
                :style="{
                  background: 'linear-gradient(to right, var(--accent-green), var(--blue), var(--blue-dark), var(--accent-green))',
                  WebkitBackgroundClip: 'text',
                  WebkitTextFillColor: 'transparent',
                  backgroundSize: '200% 200%',
                  animation: 'gradient-shift 4s ease infinite'
                }"
              >
                Crypto Experience?
              </span>
            </Motion>

            <Motion tag="p" class="text-lg text-slate-300 mb-8" :variants="fadeInUp">
              Join millions of traders worldwide who trust BitChest for secure, fast, and innovative cryptocurrency trading.
              <span class="block mt-2 text-app-secondary font-semibold">No minimum deposit required.</span>
            </Motion>

            <Motion tag="div" :variants="fadeInUp" :while-hover="{ scale: 1.05, y: -2 }" :while-tap="{ scale: 0.98 }">
              <RouterLink
                data-cta-button
                to="/signup"
                class="inline-flex items-center text-white px-8 py-4 rounded-xl font-semibold transition-all hover:shadow-xl relative overflow-hidden group"
                :style="primaryButtonStyle"
                @mouseenter="primaryHover(true)"
                @mouseleave="primaryHover(false)"
              >
                Create Free Account
                <Motion tag="div" :animate="{ x: [0, 5, 0] }" :transition="{ repeat: Infinity, duration: 1.5 }">
                  <ArrowRight class="ml-2 h-5 w-5" />
                </Motion>
                <div class="absolute inset-0 rounded-xl bg-white opacity-0 group-hover:opacity-10 transition-opacity duration-300 pointer-events-none"></div>
                <div class="absolute inset-0 -translate-x-full group-hover:translate-x-full transition-transform duration-700 bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>
              </RouterLink>
            </Motion>

            <Motion tag="div" class="flex flex-wrap gap-6 mt-10" :variants="staggerContainer">
              <Motion
                v-for="(item, i) in leftBullets"
                :key="i"
                tag="div"
                class="flex items-center space-x-2 text-slate-400 hover:text-white transition-colors"
                :variants="fadeInUp"
                :while-hover="{ scale: 1.1 }"
              >
                <Motion
                  tag="div"
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
              Platform Highlights
            </Motion>

            <Motion tag="div" class="grid sm:grid-cols-2 gap-6" :variants="staggerContainer">
              <Motion
                v-for="(card, i) in platformCards"
                :key="i"
                tag="div"
                class="bg-slate-800/50 backdrop-blur-sm p-6 rounded-xl border border-slate-700 transition-all hover:shadow-lg"
                :style="awardCardStyle"
                :variants="fadeInUp"
                :while-hover="{ y: -5 }"
                @mouseenter="awardHover($event, true)"
                @mouseleave="awardHover($event, false)"
              >
                <Motion tag="div" class="mb-4" :animate="{ rotate: [0, 10, -10, 0] }" :transition="{ duration: 5, repeat: Infinity }">
                  <component :is="card.icon" class="h-8 w-8 text-app-secondary" />
                </Motion>

                <h4 class="text-white font-semibold mb-2">{{ card.title }}</h4>
                <p class="text-slate-400 text-sm">{{ card.subtitle }}</p>

                <RouterLink
                  v-if="card.link"
                  :to="card.link"
                  class="mt-4 flex items-center text-app-secondary hover:text-white transition-colors group"
                >
                  Learn more
                  <ChevronRight class="ml-1 h-4 w-4 group-hover:translate-x-1 transition-transform" />
                </RouterLink>
              </Motion>
            </Motion>
          </Motion>
        </Motion>
      </Motion>
    </div>

    <div v-else class="relative z-10 max-w-6xl mx-auto">
      <div class="grid lg:grid-cols-2 gap-8 lg:gap-16">
        <div class="flex flex-col justify-center">
          <div
            class="inline-flex items-center gap-2 px-4 py-2 rounded-full border mb-6 w-fit"
            :style="{
              backgroundColor: 'rgba(56, 97, 140, 0.15)',
              borderColor: 'var(--blue)',
              boxShadow: '0 0 20px rgba(53, 167, 255, 0.2)'
            }"
          >
            <span class="h-2 w-2 rounded-full animate-pulse" :style="{ backgroundColor: 'var(--accent-green)' }"></span>
            <span class="text-sm font-medium" style="color: var(--blue)">BitChest Platform — Live</span>
          </div>
          <h2 class="text-3xl md:text-5xl font-bold mb-6 text-app-secondary leading-tight">Ready to Transform Your Crypto Experience?</h2>
          <p class="text-lg text-slate-300 mb-8">Join millions of traders worldwide. No minimum deposit required.</p>
          <RouterLink
            data-cta-button
            to="/signup"
            class="inline-flex items-center text-white px-8 py-4 rounded-lg font-semibold"
            :style="primaryButtonStyle"
          >
            Create Free Account
            <ArrowRight class="ml-2 h-5 w-5" />
          </RouterLink>
        </div>
        <div class="grid sm:grid-cols-2 gap-6">
          <div
            v-for="(card, i) in platformCards"
            :key="i"
            class="bg-slate-800/50 p-6 rounded-xl border border-slate-700"
          >
            <component :is="card.icon" class="h-8 w-8 text-app-secondary mb-4" />
            <h4 class="text-white font-semibold mb-2">{{ card.title }}</h4>
            <p class="text-slate-400 text-sm">{{ card.subtitle }}</p>
            <RouterLink v-if="card.link" :to="card.link" class="mt-4 inline-flex items-center text-app-secondary">
              Learn more
              <ChevronRight class="ml-1 h-4 w-4" />
            </RouterLink>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom decorative line -->
    <div class="absolute bottom-0 left-0 w-full h-1 opacity-20" :style="{ background: 'linear-gradient(to right, transparent, var(--blue-dark), var(--blue), var(--blue-dark), transparent)' }"></div>
  </section>
</template>

<script setup lang="ts">
import { Motion } from '@motionone/vue';
import { RouterLink } from 'vue-router';
import {
  ArrowRight, Shield, Zap, Users,
  Wallet, TrendingUp, ChevronRight,
  BarChart2, Lock
} from 'lucide-vue-next';
import SectionBackground from './SectionBackground.vue';

const fadeInUp = { initial: { y: 60, opacity: 0 }, animate: { y: 0, opacity: 1 }, transition: { duration: 0.6, ease: 'easeOut' } };
const staggerContainer = { animate: { transition: { staggerChildren: 0.1 } } };
const sectionInitial = { opacity: 0 };
const sectionAnimate = { opacity: 1 };

const leftBullets = [
  { icon: Shield, text: 'Bank-level security' },
  { icon: Zap, text: 'Instant trades' },
  { icon: Users, text: '24/7 Support' }
];

const platformCards = [
  { icon: Wallet, title: '€500 Starting Balance', subtitle: 'Every new account is credited for the prototype phase.', link: '/signup' },
  { icon: BarChart2, title: '10 Cryptocurrencies', subtitle: 'BTC, ETH, XRP, BCH, ADA, LTC, NEM, XLM, IOTA, DASH.', link: null },
  { icon: TrendingUp, title: 'Real-time Charts', subtitle: 'Track price evolution over the last 30 days for every asset.', link: null },
  { icon: Lock, title: 'Secure Admin Panel', subtitle: 'Separate interfaces for admins and clients. RBAC enforced.', link: '/support' }
];

const primaryButtonStyle = {
  background: 'linear-gradient(to right, var(--blue), var(--blue-dark))',
  boxShadow: '0 0 30px rgba(53, 167, 255, 0.3), 0 0 20px rgba(56, 97, 140, 0.25)'
};

const awardCardStyle = { '--hover-border': 'var(--blue)' } as Record<string, string>;

function primaryHover(enter = true) {}

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

const MotionAvailable = typeof Motion !== 'undefined' && Motion !== null;
</script>

<style scoped>
@keyframes gradient-shift {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}

.delay-1000 { animation-delay: 1s; }
.delay-2000 { animation-delay: 2s; }
</style>
