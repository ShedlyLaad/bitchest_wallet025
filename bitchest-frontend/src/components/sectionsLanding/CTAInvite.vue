<template>
  <section class="relative py-20 md:py-32 overflow-hidden" :style="{ background: 'linear-gradient(135deg, #0a0f1a 0%, #1a2332 50%, #0a0f1a 100%)' }">
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

    <!-- Animated background orbs with brand colors -->
    <div class="absolute inset-0 pointer-events-none z-0">
      <div class="absolute top-1/4 left-1/4 w-96 h-96 rounded-full blur-3xl opacity-10 animate-pulse" :style="{ backgroundColor: 'var(--blue-dark)' }"></div>
      <div class="absolute bottom-1/3 right-1/4 w-80 h-80 rounded-full blur-3xl opacity-10 animate-pulse delay-1000" :style="{ backgroundColor: 'var(--blue)' }"></div>
      <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full blur-3xl opacity-5 animate-pulse delay-2000" :style="{ backgroundColor: 'var(--accent-green)' }"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <Motion
        tag="div"
        class="text-center"
        :initial="{ opacity: 0, y: 30 }"
        :while-in-view="{ opacity: 1, y: 0 }"
        :viewport="{ once: true }"
        :transition="{ duration: 0.8, ease: 'easeOut' }"
      >
        <!-- Badge -->
        <Motion
          tag="div"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-full border mb-8 transition-all duration-300 hover:scale-105"
          :style="{
            backgroundColor: 'rgba(56, 97, 140, 0.15)',
            borderColor: 'var(--blue)',
            boxShadow: '0 0 20px rgba(53, 167, 255, 0.2)'
          }"
          :while-hover="{ scale: 1.05 }"
        >
          <span class="h-2 w-2 rounded-full animate-pulse" :style="{ backgroundColor: 'var(--accent-green)' }"></span>
          <span class="text-sm font-medium" style="color: var(--blue)">Join the Revolution</span>
        </Motion>

        <!-- Main heading -->
        <h2 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight">
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
        </h2>

        <!-- Description -->
        <p class="text-lg md:text-xl text-gray-300 max-w-3xl mx-auto mb-12 leading-relaxed">
          Join millions of traders worldwide who trust BitChest for secure, fast, and innovative cryptocurrency trading.
          <span class="block mt-3 text-app-secondary font-semibold">Start your journey today — No minimum deposit required.</span>
        </p>

        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-16">
          <Motion
            tag="router-link"
            to="/signup"
            class="group relative inline-flex items-center justify-center text-white px-10 py-5 rounded-xl font-semibold transition-all duration-300 transform hover:scale-105 shadow-xl overflow-hidden"
            :style="{
              background: 'linear-gradient(to right, var(--blue), var(--blue-dark))',
              boxShadow: '0 0 30px rgba(53, 167, 255, 0.3), 0 0 20px rgba(56, 97, 140, 0.25)'
            }"
            :while-hover="{ scale: 1.05, y: -2 }"
            :while-tap="{ scale: 0.98 }"
          >
            <span class="relative z-10">Create Free Account</span>
            <ArrowRight class="ml-2 h-5 w-5 transition-transform group-hover:translate-x-1 relative z-10" />
            <!-- Animated background on hover -->
            <div class="absolute inset-0 rounded-xl bg-white opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
            <!-- Shine effect -->
            <div class="absolute inset-0 -translate-x-full group-hover:translate-x-full transition-transform duration-700 bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>
          </Motion>

          <Motion
            tag="router-link"
            to="/features"
            class="group relative inline-flex items-center justify-center px-10 py-5 rounded-xl font-semibold transition-all duration-300 backdrop-blur-sm bg-white/5 border-2"
            :style="{ borderColor: 'var(--blue)', color: 'var(--blue)' }"
            :while-hover="{ scale: 1.05, y: -2 }"
            :while-tap="{ scale: 0.98 }"
          >
            <span>Explore Features</span>
            <ArrowRight class="ml-2 h-5 w-5 transition-transform group-hover:translate-x-1" />
          </Motion>
        </div>

        <!-- Feature highlights -->
        <Motion
          tag="div"
          class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto"
          :initial="{ opacity: 0, y: 20 }"
          :while-in-view="{ opacity: 1, y: 0 }"
          :viewport="{ once: true }"
          :transition="{ duration: 0.6, delay: 0.3 }"
        >
          <Motion
            v-for="(feature, index) in features"
            :key="index"
            tag="div"
            class="flex items-center gap-3 p-4 rounded-xl backdrop-blur-sm bg-white/5 border border-white/10 transition-all duration-300 hover:bg-white/10 hover:border-opacity-40"
            :style="{ borderColor: feature.borderColor }"
            :initial="{ opacity: 0, y: 20 }"
            :while-in-view="{ opacity: 1, y: 0 }"
            :viewport="{ once: true }"
            :transition="{ duration: 0.5, delay: 0.4 + index * 0.1 }"
            :while-hover="{ y: -5, scale: 1.02 }"
          >
            <div
              class="p-2 rounded-lg"
              :style="{
                background: `linear-gradient(135deg, ${feature.colorStart}, ${feature.colorEnd})`,
                opacity: 0.8
              }"
            >
              <component :is="feature.icon" class="h-6 w-6 text-white" />
            </div>
            <span class="text-white font-medium">{{ feature.text }}</span>
          </Motion>
        </Motion>
      </Motion>
    </div>

    <!-- Bottom decorative line -->
    <div class="absolute bottom-0 left-0 w-full h-1 opacity-20" :style="{ background: 'linear-gradient(to right, transparent, var(--blue-dark), var(--blue), var(--blue-dark), transparent)' }"></div>
  </section>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { Motion } from '@motionone/vue';
import { RouterLink } from 'vue-router';
import { ArrowRight, Shield, Zap, Users, TrendingUp, Lock } from 'lucide-vue-next';

const features = [
  {
    icon: Shield,
    text: 'Bank-Level Security',
    colorStart: 'var(--blue-dark)',
    colorEnd: 'var(--blue)',
    borderColor: 'rgba(53, 167, 255, 0.3)'
  },
  {
    icon: Zap,
    text: 'Lightning Fast',
    colorStart: 'var(--blue)',
    colorEnd: 'var(--blue-dark)',
    borderColor: 'rgba(53, 167, 255, 0.3)'
  },
  {
    icon: TrendingUp,
    text: '24/7 Trading',
    colorStart: 'var(--blue)',
    colorEnd: 'var(--blue-dark)',
    borderColor: 'rgba(56, 97, 140, 0.3)'
  }
];

// Particles animation is handled by useAnimatedBackground composable
</script>

<style scoped>
@keyframes gradient-shift {
  0%, 100% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
}

.delay-1000 {
  animation-delay: 1s;
}

.delay-2000 {
  animation-delay: 2s;
}
</style>