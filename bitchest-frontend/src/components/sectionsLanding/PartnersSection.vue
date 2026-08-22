<template>
  <ScrollReveal width="100%">
    <section class="relative py-16 md:py-24 overflow-hidden bg-gray-900">
      <SectionBackground :orbs="[{ size: '420px', color: 'var(--blue)', top: '-15%', right: '-10%' }]" />

      <div class="relative max-w-[90vw] mx-auto">
        <div class="text-center mb-8 md:mb-12">
          <h2 class="text-4xl font-bold text-white inline-block bg-clip-text text-transparent" :style="{ background: 'linear-gradient(to right, var(--blue), var(--blue-dark), var(--accent-green))', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent' }">
            Supported Cryptocurrencies
          </h2>
          <div class="mt-4 text-gray-400">Trade the most popular digital assets with real-time prices</div>
        </div>

        <div class="relative">
          <!-- Loading State -->
          <div v-if="loading" class="flex items-center justify-center py-12">
            <div class="flex space-x-2">
              <div class="w-3 h-3 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0s"></div>
              <div class="w-3 h-3 bg-green-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
              <div class="w-3 h-3 bg-blue-dark rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else-if="cryptos.length === 0" class="flex items-center justify-center py-12 text-gray-400">
            No market data available
          </div>

          <!-- Crypto Cards -->
          <div v-else class="flex items-stretch space-x-4 md:space-x-6 overflow-x-auto scrollbar-hide py-4 px-4 md:px-20">
            <div
              v-for="(crypto, index) in cryptos"
              :key="crypto.id || index"
              class="flex-none w-80 group relative"
            >
              <div
                class="relative h-full rounded-2xl border border-white/10 bg-gradient-to-br from-gray-800/50 via-gray-800/30 to-gray-800/50 backdrop-blur-xl p-6 transition-all duration-300 hover:border-opacity-60 hover:shadow-2xl flex flex-col justify-between group"
                :style="{ 
                  borderColor: 'rgba(53, 167, 255, 0.3)',
                  boxShadow: hoveredIndex === index ? '0 0 40px rgba(53, 167, 255, 0.2)' : 'none'
                }"
                @mouseenter="hoveredIndex = index"
                @mouseleave="hoveredIndex = -1"
              >
                <!-- Animated Background with brand colors -->
                <div class="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                  <div 
                    class="absolute inset-0 blur-3xl"
                    :style="{ background: `radial-gradient(circle at center, ${getCryptoColor(crypto.symbol)}40, rgba(53, 167, 255, 0.15), rgba(1, 255, 25, 0.1), transparent)` }"
                  ></div>
                </div>

                <!-- Content -->
                <div class="relative z-10">
                  <!-- Header -->
                  <div class="flex items-start justify-between mb-6">
                    <div 
                      class="p-3 rounded-xl border border-white/10 transition-all duration-300 group-hover:scale-110 group-hover:shadow-lg"
                      :style="{ 
                        background: `linear-gradient(145deg, ${getCryptoColor(crypto.symbol)}20, ${getCryptoColor(crypto.symbol)}10)`,
                        borderColor: `${getCryptoColor(crypto.symbol)}40`,
                        boxShadow: hoveredIndex === index ? `0 0 20px ${getCryptoColor(crypto.symbol)}30` : 'none'
                      }"
                    >
                      <img 
                        :src="getCryptoIcon(crypto.symbol)" 
                        :alt="crypto.name" 
                        class="w-10 h-10 object-contain"
                        @error="handleImageError"
                      />
                    </div>
                    <div class="text-right">
                      <div class="text-2xl font-bold text-white group-hover:scale-110 transition-transform duration-300">
                        €{{ formatPrice(crypto.price || 0) }}
                      </div>
                      <div 
                        class="text-sm font-medium transition-colors duration-300 flex items-center justify-end gap-1"
                        :style="{ color: (crypto.change24h || 0) >= 0 ? 'var(--accent-green)' : '#ff5964' }"
                      >
                        <span>{{ (crypto.change24h || 0) >= 0 ? '↑' : '↓' }}</span>
                        <span>{{ formatPercentage(Math.abs(crypto.change24h || 0)) }}%</span>
                      </div>
                    </div>
                  </div>

                  <!-- Info -->
                  <h3 class="text-xl font-bold text-white mb-1">{{ crypto.name }}</h3>
                  <p class="text-gray-400 text-sm mb-3">{{ crypto.symbol }}</p>
                  
                  <!-- Market Data -->
                  <div class="grid grid-cols-2 gap-3 mt-4 pt-4 border-t border-white/10">
                    <div>
                      <div class="text-xs text-gray-500 mb-1">Market Cap</div>
                      <div class="text-sm font-semibold text-white">
                        {{ formatMarketCap(crypto.marketCap || 0) }}
                      </div>
                    </div>
                    <div>
                      <div class="text-xs text-gray-500 mb-1">24h Volume</div>
                      <div class="text-sm font-semibold text-white">
                        {{ formatVolume(crypto.volume24h || 0) }}
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Hover Effects -->
                <div class="relative z-10 mt-6 flex items-center justify-between text-sm">
                  <span class="text-gray-500 group-hover:text-gray-300 transition-colors">Live Price</span>
                  <div 
                    class="opacity-0 group-hover:opacity-100 transition-all duration-300 px-3 py-1 rounded-full transform group-hover:translate-x-1"
                    :style="{ 
                      background: 'rgba(53, 167, 255, 0.15)',
                      color: 'var(--blue)',
                      border: '1px solid rgba(53, 167, 255, 0.3)'
                    }"
                  >
                    Trade Now →
                  </div>
                </div>

                <!-- Bottom accent line on hover -->
                <div 
                  class="absolute bottom-0 left-0 right-0 h-1 opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-b-2xl"
                  :style="{ background: `linear-gradient(to right, ${getCryptoColor(crypto.symbol)}, var(--blue), var(--blue-dark))` }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </ScrollReveal>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import ScrollReveal from '../ScrollReveal.vue';
import SectionBackground from './SectionBackground.vue';
import type { CryptoCurrency } from '../../types';
import { getPublicMarket } from '../../services/api';

// State
const cryptos = ref<CryptoCurrency[]>([]);
const loading = ref(true);
const hoveredIndex = ref<number>(-1);

// Crypto icon mapping
const getCryptoIcon = (symbol: string): string => {
  const iconMap: Record<string, string> = {
    'BTC': '/src/assets/bitcoin.png',
    'ETH': '/src/assets/ethereum.png',
    'XRP': '/src/assets/ripple.png',
    'BCH': '/src/assets/bitcoin-cash.png',
    'ADA': '/src/assets/cardano.png',
    'LTC': '/src/assets/litecoin.png',
    'XEM': '/src/assets/nem.png',
    'XLM': '/src/assets/stellar.png',
    'MIOTA': '/src/assets/iota.png',
    'DASH': '/src/assets/dash.png'
  };
  return iconMap[symbol.toUpperCase()] || '/src/assets/bitcoin.png';
};

// Crypto color mapping
const getCryptoColor = (symbol: string): string => {
  const colorMap: Record<string, string> = {
    'BTC': '#F7931A',
    'ETH': '#627EEA',
    'XRP': '#0085c3',
    'BCH': '#0ac18e',
    'ADA': '#0033ad',
    'LTC': '#345d9d',
    'XEM': '#67B2E8',
    'XLM': '#7d00ff',
    'MIOTA': '#00d4ff',
    'DASH': '#008de4'
  };
  return colorMap[symbol.toUpperCase()] || '#35a7ff';
};

// Format price (EUR format)
const formatPrice = (price: number): string => {
  if (!price || isNaN(price) || price <= 0) return '0.00';
  
  if (price >= 1000) {
    return new Intl.NumberFormat('fr-FR', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(price);
  } else if (price >= 1) {
    return new Intl.NumberFormat('fr-FR', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 4
    }).format(price);
  } else {
    return new Intl.NumberFormat('fr-FR', {
      minimumFractionDigits: 4,
      maximumFractionDigits: 8
    }).format(price);
  }
};

// Format percentage
const formatPercentage = (value: number): string => {
  if (value === null || value === undefined || isNaN(value)) return '0.00';
  return Math.abs(value).toFixed(2);
};

// Format market cap
const formatMarketCap = (value: number): string => {
  if (!value || isNaN(value) || value <= 0) return '€0';
  if (value >= 1e12) return `€${(value / 1e12).toFixed(2)}T`;
  if (value >= 1e9) return `€${(value / 1e9).toFixed(2)}B`;
  if (value >= 1e6) return `€${(value / 1e6).toFixed(2)}M`;
  if (value >= 1e3) return `€${(value / 1e3).toFixed(2)}K`;
  return `€${value.toFixed(2)}`;
};

// Format volume
const formatVolume = (value: number): string => {
  if (!value || isNaN(value) || value <= 0) return '€0';
  if (value >= 1e9) return `€${(value / 1e9).toFixed(2)}B`;
  if (value >= 1e6) return `€${(value / 1e6).toFixed(2)}M`;
  if (value >= 1e3) return `€${(value / 1e3).toFixed(2)}K`;
  return `€${value.toFixed(2)}`;
};

// Handle image error
const handleImageError = (event: Event) => {
  const img = event.target as HTMLImageElement;
  img.src = '/src/assets/bitcoin.png'; // Fallback icon
};

// Load cryptos from API (public route for landing page)
const loadCryptos = async () => {
  try {
    loading.value = true;
    const data = await getPublicMarket(true);
    
    if (Array.isArray(data) && data.length > 0) {
      // Normaliser et formater les données
      cryptos.value = data.slice(0, 8).map((crypto: any) => ({
        id: crypto.id ?? null,
        symbol: crypto.symbol ?? '',
        name: crypto.name ?? '',
        price: typeof crypto.price === 'number' ? Number(crypto.price) : Number(crypto.price || 0),
        change24h: typeof crypto.change24h === 'number' 
          ? Number(crypto.change24h) 
          : Number(crypto.change24h || 0),
        marketCap: typeof crypto.marketCap === 'number' ? Number(crypto.marketCap) : Number(crypto.marketCap || 0),
        volume24h: typeof crypto.volume24h === 'number' ? Number(crypto.volume24h) : Number(crypto.volume24h || 0),
      }));
    } else {
      cryptos.value = [];
    }
  } catch (error: any) {
    console.error('Error loading cryptos:', error);
    // Fallback to empty array if API fails
    cryptos.value = [];
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadCryptos();
});
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>