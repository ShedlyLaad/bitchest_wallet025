<template>
  <div class="space-y-4 sm:space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-white to-gray-300 bg-clip-text text-transparent">
          Market Management
        </h1>
        <p class="text-gray-400 mt-1 text-sm sm:text-base">Monitor cryptocurrency prices</p>
      </div>
      <div class="flex items-center gap-3">
        <div class="text-xs text-gray-400 bg-gray-800/50 px-3 py-1.5 rounded-lg border border-gray-700/50">
          <span class="font-medium text-white">Last update:</span>
          <span class="ml-1">{{ lastUpdatedLabel }}</span>
        </div>
        <button
          class="px-4 py-2 text-sm rounded-lg bg-blue-600 hover:bg-blue-700 transition-colors disabled:opacity-50 font-medium flex items-center gap-2"
          :disabled="loading"
          @click="loadCryptos"
        >
          <RefreshCw :class="['h-4 w-4', loading && 'animate-spin']" />
          {{ loading ? 'Refreshing...' : 'Refresh' }}
        </button>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="errorMessage" class="bg-red-900/20 border-l-4 border-red-500 rounded-lg p-4 text-red-300 shadow-lg">
      <div class="flex items-center gap-2">
        <AlertCircle class="h-5 w-5 flex-shrink-0" />
        <span>{{ errorMessage }}</span>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
      <!-- Crypto List -->
      <div class="lg:col-span-1">
        <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl border border-gray-700 shadow-lg overflow-hidden">
          <div class="p-4 border-b border-gray-700 bg-gray-800/50">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold text-white">Cryptocurrencies</h3>
                <p class="text-xs text-gray-400 mt-1">{{ adminCryptosLocal.length }} assets</p>
              </div>
              <div class="px-2 py-1 bg-green-500/20 border border-green-500/30 rounded-lg">
                <span class="text-xs font-medium text-green-400">Live</span>
              </div>
            </div>
          </div>
          <div class="overflow-x-auto max-h-[calc(100vh-300px)] overflow-y-auto crypto-table-container">
            <div v-if="errorMessage" class="p-4 text-sm text-red-400 border-b border-gray-700 bg-red-900/10">{{ errorMessage }}</div>
            <div v-else-if="loading" class="p-4 text-sm text-gray-400 border-b border-gray-700 flex items-center gap-2">
              <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-500"></div>
              <span>Loading cryptos...</span>
            </div>
            <div v-else class="p-2">
              <div
                v-for="crypto in adminCryptosLocal"
                :key="crypto.id"
                @click="setSelectedCrypto(crypto)"
                :class="[
                  'group relative mb-2 rounded-lg border transition-all duration-300 cursor-pointer overflow-hidden',
                  selectedCrypto?.id === crypto.id
                    ? 'bg-gradient-to-r from-blue-600/20 to-blue-500/10 border-blue-500/50 shadow-lg shadow-blue-500/20 scale-[1.02]'
                    : 'bg-gray-800/30 border-gray-700/50 hover:scale-105 hover:bg-gray-700/40 hover:border-blue-500/30 hover:shadow-xl hover:shadow-blue-500/10',
                  crypto.isUpdating && crypto.animationClass === 'price-up' ? 'animate-bounce-up bg-green-500/10 border-green-500/50' : '',
                  crypto.isUpdating && crypto.animationClass === 'price-down' ? 'animate-bounce-down bg-red-500/10 border-red-500/50' : '',
                  crypto.isUpdating && crypto.animationClass === 'change-up' ? 'animate-pulse-change bg-yellow-500/10 border-yellow-500/50' : ''
                ]"
              >
                <!-- Gradient overlay on hover -->
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/0 to-purple-500/0 group-hover:from-blue-500/5 group-hover:to-purple-500/5 transition-all duration-300 pointer-events-none"></div>
                
                <div class="relative p-4 flex items-center justify-between">
                  <!-- Left: Crypto Info -->
                  <div class="flex items-center gap-3 flex-1 min-w-0">
                    <!-- Icon Container -->
                    <div class="relative flex-shrink-0">
                      <div :class="[
                        'w-12 h-12 rounded-xl flex items-center justify-center overflow-hidden border-2 transition-all duration-300',
                        selectedCrypto?.id === crypto.id
                          ? 'border-blue-500/50 bg-blue-500/10 scale-110'
                          : 'border-gray-600/50 bg-gray-700/50 group-hover:border-blue-400/50 group-hover:scale-110 group-hover:bg-blue-500/10'
                      ]">
                        <img
                          v-if="crypto.icon"
                          :src="crypto.icon"
                          :alt="crypto.symbol"
                          class="w-full h-full object-contain p-1"
                          @error="(e: any) => e.target.style.display = 'none'"
                        />
                        <span v-else class="text-white font-bold text-lg">{{ crypto.symbol.charAt(0) }}</span>
                      </div>
                      <!-- Active indicator -->
                      <div v-if="selectedCrypto?.id === crypto.id" class="absolute -top-1 -right-1 w-3 h-3 bg-blue-500 rounded-full border-2 border-gray-900 animate-pulse"></div>
                    </div>
                    
                    <!-- Crypto Details -->
                    <div class="flex flex-col min-w-0 flex-1">
                      <div class="flex items-center gap-2 mb-0.5">
                        <span class="text-white font-bold text-base group-hover:text-blue-400 transition-colors">{{ crypto.symbol }}</span>
                        <span v-if="selectedCrypto?.id === crypto.id" class="px-1.5 py-0.5 bg-blue-500/20 text-blue-400 text-xs font-medium rounded">Active</span>
                      </div>
                      <span class="text-gray-400 text-xs truncate">{{ crypto.name }}</span>
                    </div>
                  </div>

                  <!-- Right: Price and Change -->
                  <div class="flex items-center gap-4 flex-shrink-0">
                    <!-- Price -->
                    <div class="text-right">
                      <div 
                        :class="[
                          'text-white font-semibold text-sm group-hover:text-blue-300 transition-all duration-300',
                          crypto.isUpdating && crypto.animationClass === 'price-up' ? 'text-green-400 animate-pulse' : ''
                        ]"
                      >
                        {{ formatEUR(crypto.price) }}
                        <span 
                          v-if="crypto.previousPrice && crypto.price && crypto.price > crypto.previousPrice"
                          class="ml-1 text-green-400 text-xs animate-fade-in"
                        >
                          ↑
                        </span>
                        <span 
                          v-else-if="crypto.previousPrice && crypto.price && crypto.price < crypto.previousPrice"
                          class="ml-1 text-red-400 text-xs animate-fade-in"
                        >
                          ↓
                        </span>
                      </div>
                    </div>
                    
                    <!-- 24h Change -->
                    <div class="text-right">
                      <div
                        :class="[
                          'inline-flex items-center gap-1 px-3 py-1.5 rounded-lg font-semibold text-sm transition-all duration-300 relative',
                          (crypto.change24h ?? 0) >= 0
                            ? 'bg-green-500/20 text-green-400 border border-green-500/30 group-hover:bg-green-500/30 group-hover:shadow-lg group-hover:shadow-green-500/20'
                            : 'bg-red-500/20 text-red-400 border border-red-500/30 group-hover:bg-red-500/30 group-hover:shadow-lg group-hover:shadow-red-500/20',
                          crypto.isUpdating && crypto.animationClass === 'change-up' ? 'animate-pulse-change scale-110' : ''
                        ]"
                      >
                        <span>{{ (crypto.change24h ?? 0) >= 0 ? '+' : '' }}{{ (crypto.change24h ?? 0).toFixed(2) }}%</span>
                        <!-- Indicateur de changement -->
                        <span 
                          v-if="crypto.previousChange24h !== undefined && crypto.change24h !== undefined && 
                                Math.abs(crypto.change24h) > Math.abs(crypto.previousChange24h)"
                          :class="[
                            'absolute -top-1 -right-1 w-2 h-2 rounded-full animate-ping',
                            crypto.change24h >= 0 ? 'bg-green-400' : 'bg-red-400'
                          ]"
                        ></span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Chart Section -->
      <div class="lg:col-span-2">
        <div v-if="selectedCrypto" class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl border border-gray-700 shadow-lg overflow-hidden">
          <!-- Professional Trading Chart -->
          <ProfessionalTradingChart
            :symbol="selectedCrypto.symbol"
            :crypto-name="selectedCrypto.name"
            :price-data-with-dates="history"
            :current-price="selectedCrypto.price ?? 0"
            :change24h="selectedCrypto.change24h ?? 0"
            :crypto-icon="selectedCrypto.icon"
            :market-cap="selectedCrypto.marketCap ? Number(selectedCrypto.marketCap) : undefined"
            :timeframe="timeframe"
            @timeframe-changed="handleTimeframeChange"
            currency="EUR"
            :height="450"
          />
        </div>
        <div v-else class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl border border-gray-700 shadow-lg p-12 flex items-center justify-center min-h-[450px]">
          <div class="text-center">
            <TrendingUp class="h-16 w-16 text-gray-600 mx-auto mb-4" />
            <p class="text-gray-400 text-lg font-medium">Select a cryptocurrency to view chart</p>
            <p class="text-gray-500 text-sm mt-2">Choose from the list on the left</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { RefreshCw, TrendingUp, AlertCircle } from 'lucide-vue-next';
import { formatEUR } from '@/utils/format';
import { getCryptoIcon } from '@/utils/cryptoIcons';
import ProfessionalTradingChart from '@/components/ProfessionalTradingChart.vue';
import { getAdminCryptoHistory, getAdminCryptos } from '@/services/api';
import type { CryptoCurrency, CryptoPricePoint } from '@/types';

type AdminCryptoUI = CryptoCurrency & {
  icon?: string;
  previousPrice?: number;
  previousChange24h?: number;
  isUpdating?: boolean;
  animationClass?: string;
};

const adminCryptosLocal = ref<AdminCryptoUI[]>([]);
const selectedCrypto = ref<AdminCryptoUI | null>(null);
const history = ref<CryptoPricePoint[]>([]);
const loading = ref(false);
const historyLoading = ref(false);
const errorMessage = ref('');
const lastUpdated = ref<Date | null>(null);
const timeframe = ref('30d'); // Default to 30 days
let refreshTimer: ReturnType<typeof setInterval> | null = null;

async function loadCryptos() {
  loading.value = true;
  errorMessage.value = '';
  try {
    const data = await getAdminCryptos();
    
    // Stocker les prix précédents pour détecter les changements
    const previousPrices = new Map(
      adminCryptosLocal.value.map(c => [c.symbol, { price: c.price, change24h: c.change24h }])
    );
    
    adminCryptosLocal.value = data.map((crypto) => {
      const prev = previousPrices.get(crypto.symbol);
      const isPriceIncreased = prev && crypto.price && prev.price && crypto.price > prev.price;
      const isPriceDecreased = prev && crypto.price && prev.price && crypto.price < prev.price;
      const isChangeIncreased = prev && crypto.change24h !== undefined && prev.change24h !== undefined && 
                                Math.abs(crypto.change24h) > Math.abs(prev.change24h ?? 0);
      
      return {
        ...crypto,
        icon: getCryptoIcon(crypto.symbol),
        previousPrice: prev?.price,
        previousChange24h: prev?.change24h,
        isUpdating: false,
        animationClass: isPriceIncreased ? 'price-up' : isPriceDecreased ? 'price-down' : isChangeIncreased ? 'change-up' : '',
      };
    });
    
    // Animer les changements
    adminCryptosLocal.value.forEach((crypto) => {
      if (crypto.animationClass) {
        crypto.isUpdating = true;
        setTimeout(() => {
          crypto.isUpdating = false;
          crypto.animationClass = '';
        }, 1500);
      }
    });
    
    // Trier par change24h (croissant pour mettre les valeurs en baisse en haut, puis les hausses)
    // Les valeurs négatives (baisse) en haut, les positives (hausse) en bas
    adminCryptosLocal.value.sort((a, b) => {
      const changeA = a.change24h ?? 0;
      const changeB = b.change24h ?? 0;
      
      // Si les deux sont négatifs, trier du plus négatif au moins négatif
      if (changeA < 0 && changeB < 0) {
        return changeA - changeB;
      }
      // Si les deux sont positifs, trier du plus positif au moins positif
      if (changeA >= 0 && changeB >= 0) {
        return changeB - changeA;
      }
      // Les négatifs avant les positifs
      return changeA < 0 ? -1 : 1;
    });
    
    // Mettre à jour la crypto sélectionnée si elle existe toujours
    if (selectedCrypto.value) {
      const updated = adminCryptosLocal.value.find(c => c.symbol === selectedCrypto.value?.symbol);
      if (updated) {
        selectedCrypto.value = updated;
      }
    }
    
    if (adminCryptosLocal.value.length && !selectedCrypto.value) {
      selectedCrypto.value = adminCryptosLocal.value[0];
      await loadHistory(selectedCrypto.value.symbol);
    }
    lastUpdated.value = new Date();
  } catch (e: any) {
    errorMessage.value = e?.response?.data?.message || 'Impossible de charger les cryptos';
  } finally {
    loading.value = false;
  }
}

async function loadHistory(symbol: string) {
  historyLoading.value = true;
  try {
    history.value = await getAdminCryptoHistory(symbol, timeframe.value);
  } catch {
    history.value = [];
  } finally {
    historyLoading.value = false;
  }
}

async function setSelectedCrypto(c: AdminCryptoUI) {
  selectedCrypto.value = c;
  await loadHistory(c.symbol);
}

async function handleTimeframeChange(newTimeframe: string) {
  timeframe.value = newTimeframe;
  if (selectedCrypto.value) {
    await loadHistory(selectedCrypto.value.symbol);
  }
}


const lastUpdatedLabel = computed(() => {
  if (!lastUpdated.value) return '—';
  return lastUpdated.value.toLocaleTimeString();
});

onMounted(() => {
  loadCryptos();
  // Refresh toutes les 30 secondes pour des données live
  refreshTimer = setInterval(loadCryptos, 30000);
});

onUnmounted(() => {
  if (refreshTimer) clearInterval(refreshTimer);
});
</script>

<style scoped>
/* Custom scrollbar */
.crypto-table-container::-webkit-scrollbar {
  width: 8px;
}

.crypto-table-container::-webkit-scrollbar-track {
  background: rgba(31, 41, 55, 0.3);
  border-radius: 4px;
}

.crypto-table-container::-webkit-scrollbar-thumb {
  background: rgba(107, 114, 128, 0.5);
  border-radius: 4px;
  transition: background 0.2s;
}

.crypto-table-container::-webkit-scrollbar-thumb:hover {
  background: rgba(59, 130, 246, 0.6);
}

/* Smooth animations */
.crypto-table-container > div > div {
  transform-origin: center;
  will-change: transform;
}

/* Glow effect on selected */
.group[class*="border-blue-500"] {
  animation: selectedGlow 2s ease-in-out infinite;
}

@keyframes selectedGlow {
  0%, 100% {
    box-shadow: 0 0 20px rgba(59, 130, 246, 0.2);
  }
  50% {
    box-shadow: 0 0 30px rgba(59, 130, 246, 0.3);
  }
}

/* Animations pour les changements de prix */
@keyframes bounce-up {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-8px);
  }
}

@keyframes bounce-down {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(8px);
  }
}

.animate-bounce-down {
  animation: bounce-down 0.6s ease-in-out;
}

@keyframes pulse-change {
  0%, 100% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.05);
    opacity: 0.9;
  }
}

@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-bounce-up {
  animation: bounce-up 0.6s ease-in-out;
}

.animate-pulse-change {
  animation: pulse-change 0.8s ease-in-out;
}

.animate-fade-in {
  animation: fade-in 0.3s ease-out;
}
</style>
