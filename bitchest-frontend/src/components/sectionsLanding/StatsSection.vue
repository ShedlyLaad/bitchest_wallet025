<template>
  <ScrollReveal width="100%">
    <section class="relative py-16 md:py-24 overflow-hidden">
      <!-- Background effects with brand colors -->
      <div class="absolute inset-0 pointer-events-none">
        <div 
          class="absolute top-1/2 left-1/4 w-64 h-64 rounded-full blur-3xl opacity-10"
          :style="{ backgroundColor: 'var(--blue)' }"
        ></div>
        <div 
          class="absolute bottom-1/4 right-1/4 w-64 h-64 rounded-full blur-3xl opacity-10"
          :style="{ backgroundColor: 'var(--accent-green)' }"
        ></div>
      </div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 z-10">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
          <div
            v-for="(stat, index) in stats"
            :key="index"
            class="group relative text-center transform transition-all duration-500 hover:scale-105"
            :style="{ transitionDelay: `${index * 100}ms` }"
          >
            <!-- Card with gradient border -->
            <div class="relative bg-gray-800/50 backdrop-blur-xl rounded-2xl p-8 border border-gray-700/30 transition-all duration-500 hover:border-opacity-60 overflow-hidden">
              <!-- Hover glow effect -->
              <div 
                class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500"
                :style="{ background: `radial-gradient(circle at center, ${stat.glowColor}20, transparent 70%)` }"
              ></div>
              
              <!-- Content -->
              <div class="relative z-10">
                <div 
                  :class="`text-4xl lg:text-5xl xl:text-6xl font-bold mb-3 transition-all duration-300 group-hover:scale-110`"
                  :style="{ color: stat.colorValue }"
                >
                  <AnimatedCounter
                    :value="stat.value"
                    :suffix="stat.suffix"
                    :prefix="stat.prefix || ''"
                    :decimals="stat.decimals || 0"
                  />
                </div>
                <div class="text-gray-300 text-base md:text-lg font-medium group-hover:text-white transition-colors">
                  {{ stat.label }}
                </div>
                
                <!-- Decorative line -->
                <div 
                  class="mt-4 h-1 w-16 mx-auto rounded-full transition-all duration-500 group-hover:w-24"
                  :style="{ backgroundColor: stat.colorValue, opacity: 0.6 }"
                ></div>
              </div>

              <!-- Animated background pattern -->
              <div class="absolute inset-0 opacity-0 group-hover:opacity-10 transition-opacity duration-500">
                <div class="absolute inset-0" :style="{ backgroundImage: `linear-gradient(45deg, ${stat.glowColor}10 25%, transparent 25%, transparent 75%, ${stat.glowColor}10 75%), linear-gradient(45deg, ${stat.glowColor}10 25%, transparent 25%, transparent 75%, ${stat.glowColor}10 75%)`, backgroundSize: '20px 20px', backgroundPosition: '0 0, 10px 10px' }"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </ScrollReveal>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import ScrollReveal from '../ScrollReveal.vue';
import AnimatedCounter from '../AnimatedCounter.vue';

const stats = ref([
  { 
    value: 7390, 
    suffix: '+', 
    label: 'Active Users', 
    color: 'text-app-secondary',
    colorValue: 'var(--blue)',
    glowColor: '#35A7FF'
  },
  { 
    value: 39, 
    suffix: 'M+', 
    label: 'Volume Traded', 
    color: 'PnL--pos', 
    prefix: '$',
    colorValue: 'var(--accent-green)',
    glowColor: '#01FF19'
  },
  { 
    value: 120, 
    suffix: '+', 
    label: 'Countries', 
    color: 'text-app-primary',
    colorValue: 'var(--blue-dark)',
    glowColor: '#38618C'
  },
  { 
    value: 99.9, 
    suffix: '%', 
    label: 'Uptime', 
    color: 'text-yellow-400', 
    decimals: 1,
    colorValue: 'var(--accent-green)',
    glowColor: '#01FF19'
  }
]);
</script>

<style scoped>
/* nothing extra for now; animations handled via Tailwind utility classes */
</style>