<template>
  <ScrollReveal width="100%">
    <section class="relative py-16 md:py-24 overflow-hidden bg-gray-900">
      <SectionBackground :particles="false" />

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 z-10">
        <div class="text-center mb-12 md:mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-white">Platform Highlights</h2>
          <p class="mt-3 text-gray-400 max-w-2xl mx-auto">What you actually get on BitChest — no marketing fluff, just the facts.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
          <div
            v-for="(item, index) in highlights"
            :key="index"
            class="group relative text-center"
          >
            <div class="relative bg-gray-800/50 backdrop-blur-xl rounded-2xl p-8 border border-gray-700/40 transition-all duration-300 hover:border-gray-600/60 h-full flex flex-col items-center">
              <div class="p-3 rounded-xl mb-4" :style="{ backgroundColor: 'rgba(53, 167, 255, 0.12)', border: '1px solid rgba(53, 167, 255, 0.25)' }">
                <component :is="item.icon" class="h-6 w-6" style="color: var(--blue)" />
              </div>

              <div class="text-3xl lg:text-4xl font-bold mb-2 text-white">
                <AnimatedCounter v-if="typeof item.value === 'number'" :value="item.value" :suffix="item.suffix || ''" />
                <span v-else>{{ item.value }}</span>
              </div>
              <div class="text-gray-400 text-sm md:text-base">{{ item.label }}</div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </ScrollReveal>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Coins, Zap, Wallet, ShieldCheck } from 'lucide-vue-next';
import ScrollReveal from '../ScrollReveal.vue';
import SectionBackground from './SectionBackground.vue';
import AnimatedCounter from '../AnimatedCounter.vue';
import { getPublicMarket } from '../../services/api';

// Only real facts here: the crypto count is fetched live, everything else is a
// static product fact (verified against the backend), never an invented statistic.
const cryptoCount = ref<number | null>(null);

const highlights = ref([
  { icon: Coins, value: '—', label: 'Supported Cryptocurrencies' },
  { icon: Zap, value: 'Live', label: 'Real-time Coinbase-sourced pricing' },
  { icon: Wallet, value: '€500', label: 'Starting balance for new accounts' },
  { icon: ShieldCheck, value: 'RBAC', label: 'Secure, role-separated admin panel' },
]);

onMounted(async () => {
  try {
    const market = await getPublicMarket();
    if (Array.isArray(market) && market.length > 0) {
      cryptoCount.value = market.length;
      highlights.value[0] = { icon: Coins, value: market.length, label: 'Supported Cryptocurrencies' };
    }
  } catch {
    // Keep the static placeholder if the public market endpoint is unavailable
  }
});
</script>
