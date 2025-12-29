<template>
  <ScrollReveal width="100%">
    <section class="py-12 md:py-20 bg-gray-800/30 backdrop-blur-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
          <div
            v-for="(stat, index) in stats"
            :key="index"
            class="text-center transform transition-all duration-700 hover:scale-110"
            :style="{ transitionDelay: `${index * 200}ms` }"
          >
            <div :class="`text-4xl lg:text-5xl font-bold ${stat.color} mb-2`">
              <AnimatedCounter
                :value="stat.value"
                :suffix="stat.suffix"
                :prefix="stat.prefix || ''"
                :decimals="stat.decimals || 0"
              />
            </div>
            <div class="text-gray-400 text-lg">{{ stat.label }}</div>
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
  { value: 7390, suffix: '+', label: 'Active Users', color: 'text-app-secondary' },
  { value: 39, suffix: 'M+', label: 'Volume Traded', color: 'PnL--pos', prefix: '$' },
  { value: 120, suffix: '+', label: 'Countries', color: 'text-app-primary' },
  { value: 78.5, suffix: '%', label: 'Uptime', color: 'text-yellow-400', decimals: 1 }
]);
</script>

<style scoped>
/* nothing extra for now; animations handled via Tailwind utility classes */
</style>