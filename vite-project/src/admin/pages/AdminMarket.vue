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
            <h3 class="text-lg font-semibold text-white">Cryptocurrencies</h3>
            <p class="text-xs text-gray-400 mt-1">{{ adminCryptosLocal.length }} assets</p>
          </div>
          <div class="overflow-x-auto max-h-[calc(100vh-300px)] overflow-y-auto">
            <div v-if="errorMessage" class="p-3 text-sm text-red-400 border-b border-gray-700">{{ errorMessage }}</div>
            <div v-else-if="loading" class="p-3 text-sm text-gray-400 border-b border-gray-700">Loading cryptos...</div>
            <table class="w-full">
              <thead class="sticky top-0 bg-gray-800/95 backdrop-blur-sm z-10">
                <tr class="border-b border-gray-700">
                  <th class="text-left p-3 text-xs text-gray-400 font-medium uppercase tracking-wider">Symbol</th>
                  <th class="text-left p-3 text-xs text-gray-400 font-medium uppercase tracking-wider">Price</th>
                  <th class="text-right p-3 text-xs text-gray-400 font-medium uppercase tracking-wider">24h</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="crypto in adminCryptosLocal"
                  :key="crypto.id"
                  @click="setSelectedCrypto(crypto)"
                  :class="[
                    'border-b border-gray-700/50 cursor-pointer transition-all',
                    selectedCrypto?.id === crypto.id
                      ? 'bg-blue-600/20 border-l-4 border-l-blue-500'
                      : 'hover:bg-gray-700/30 border-l-4 border-l-transparent'
                  ]"
                >
                  <td class="p-3">
                    <div class="flex items-center gap-2">
                      <div class="w-8 h-8 flex-shrink-0 bg-gray-700 rounded-lg flex items-center justify-center overflow-hidden border border-gray-600">
                        <img
                          v-if="crypto.icon"
                          :src="crypto.icon"
                          :alt="crypto.symbol"
                          class="w-full h-full object-contain"
                          @error="(e: any) => e.target.style.display = 'none'"
                        />
                        <span v-else class="text-white font-bold text-xs">{{ crypto.symbol.charAt(0) }}</span>
                      </div>
                      <div class="flex flex-col">
                        <span class="text-white font-semibold text-sm">{{ crypto.symbol }}</span>
                        <span class="text-gray-400 text-xs">{{ crypto.name }}</span>
                      </div>
                    </div>
                  </td>
                  <td class="p-3 text-white text-sm font-medium">{{ formatEUR(crypto.price) }}</td>
                  <td class="p-3 text-right">
                    <span
                      :class="[
                        'text-sm font-semibold px-2 py-1 rounded',
                        (crypto.change24h ?? 0) >= 0
                          ? 'PnL--pos bg-green-900/20 text-green-400'
                          : 'PnL--neg bg-red-900/20 text-red-400'
                      ]"
                    >
                      {{ (crypto.change24h ?? 0) >= 0 ? '+' : '' }}{{ (crypto.change24h ?? 0).toFixed(2) }}%
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
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
            :price-data="priceSeries"
            :current-price="selectedCrypto.price ?? 0"
            :change24h="selectedCrypto.change24h ?? 0"
            :crypto-icon="selectedCrypto.icon"
            :market-cap="selectedCrypto.marketCap ? parseFloat(selectedCrypto.marketCap) : undefined"
            currency="EUR"
            :height="450"
          />
        </div>
        <div v-else class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl border border-gray-700 shadow-lg p-12 flex items-center justify-center min-h-[450px]">
          <div class="text-center">
            <TrendingUp class="h-16 w-16 text-gray-600 mx-auto mb-4" />
            <p class="text-gray-400 text-lg">Select a cryptocurrency to view chart</p>
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
  change24h?: number;
  marketCap?: string;
  volume24h?: string;
  icon?: string;
};

const adminCryptosLocal = ref<AdminCryptoUI[]>([]);
const selectedCrypto = ref<AdminCryptoUI | null>(null);
const history = ref<CryptoPricePoint[]>([]);
const loading = ref(false);
const historyLoading = ref(false);
const errorMessage = ref('');
const lastUpdated = ref<Date | null>(null);
let refreshTimer: ReturnType<typeof setInterval> | null = null;

async function loadCryptos() {
  loading.value = true;
  errorMessage.value = '';
  try {
    const data = await getAdminCryptos();
    adminCryptosLocal.value = data.map((crypto) => ({
      ...crypto,
      icon: getCryptoIcon(crypto.symbol),
    }));
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
    history.value = await getAdminCryptoHistory(symbol);
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

const priceSeries = computed(() => {
  if (history.value.length) {
    return history.value.map((p) => Number(p.price));
  }
  const price = selectedCrypto.value?.price ?? 0;
  return Array.from({ length: 30 }, () => price);
});

const lastUpdatedLabel = computed(() => {
  if (!lastUpdated.value) return '—';
  return lastUpdated.value.toLocaleTimeString();
});

onMounted(() => {
  loadCryptos();
  refreshTimer = setInterval(loadCryptos, 30000);
});

onUnmounted(() => {
  if (refreshTimer) clearInterval(refreshTimer);
});
</script>

<style scoped>
/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: rgba(31, 41, 55, 0.5);
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: rgba(107, 114, 128, 0.5);
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: rgba(107, 114, 128, 0.7);
}
</style>
