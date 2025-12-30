<template>
  <div class="space-y-4 sm:space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold">Market Management</h1>
        <p class="text-gray-400 mt-1 text-sm sm:text-base">Monitor cryptocurrency prices</p>
      </div>
      <div class="flex items-center gap-3">
        <div class="text-xs text-gray-400">
          <span class="font-medium text-white">Last update:</span>
          <span>{{ lastUpdatedLabel }}</span>
        </div>
        <button
          class="px-3 py-2 text-sm rounded-lg bg-blue-600 hover:bg-blue-700 transition-colors disabled:opacity-50"
          :disabled="loading"
          @click="loadCryptos"
        >
          {{ loading ? 'Refreshing...' : 'Refresh' }}
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
      <div class="lg:col-span-1">
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-x-auto">
          <div v-if="errorMessage" class="p-3 text-sm text-red-400 border-b border-gray-700">{{ errorMessage }}</div>
          <div v-else-if="loading" class="p-3 text-sm text-gray-400 border-b border-gray-700">Loading cryptos...</div>
          <table class="w-full">
            <thead>
              <tr class="border-b border-gray-700">
                <th class="text-left p-4 text-gray-400 font-medium">Symbol</th>
                <th class="text-left p-4 text-gray-400 font-medium">Name</th>
                <th class="text-right p-4 text-gray-400 font-medium">Price (€)</th>
                <th class="text-right p-4 text-gray-400 font-medium">24h Change</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="crypto in adminCryptosLocal"
                :key="crypto.id"
                @click="setSelectedCrypto(crypto)"
                :class="['border-b border-gray-700 cursor-pointer transition-colors', selectedCrypto?.id === crypto.id ? 'bg-gray-700/50' : 'hover:bg-gray-700/30']"
              >
                <td class="p-4 text-white font-medium">{{ crypto.symbol }}</td>
                <td class="p-4 text-gray-300">{{ crypto.name }}</td>
                <td class="p-4 text-white text-right">{{ formatEUR(crypto.price) }}</td>
                <td class="p-4 text-right">
                  <span :class="(crypto.change24h ?? 0) >= 0 ? 'PnL--pos font-medium' : 'PnL--neg font-medium'">
                    {{ (crypto.change24h ?? 0) >= 0 ? '+' : '' }}{{ (crypto.change24h ?? 0).toFixed(2) }}%
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="lg:col-span-2">
        <div class="bg-gray-800 rounded-xl p-4 sm:p-6 border border-gray-700">
          <div class="flex items-center space-x-4 mb-4" v-if="selectedCrypto">
            <div class="w-10 h-10 sm:w-12 sm:h-12 flex-shrink-0">
              <img v-if="selectedCrypto?.icon" :src="selectedCrypto.icon" :alt="selectedCrypto.name" class="w-full h-full object-contain" />
              <div v-else class="w-full h-full bg-gray-700 rounded-lg flex items-center justify-center text-white font-bold">
                {{ selectedCrypto?.symbol }}
              </div>
            </div>
            <div>
              <h3 class="text-xl sm:text-2xl font-bold">{{ selectedCrypto.name }}</h3>
              <div class="flex items-center gap-4 text-sm text-gray-400">
                <span>{{ formatEUR(selectedCrypto.price ?? 0) }}</span>
                <span :class="(selectedCrypto.change24h ?? 0) >= 0 ? 'PnL--pos' : 'PnL--neg'">
                  {{ (selectedCrypto.change24h ?? 0) >= 0 ? '+' : '' }}{{ (selectedCrypto.change24h ?? 0).toFixed(2) }}%
                </span>
              </div>
            </div>
          </div>
          <div v-else class="text-sm text-gray-400 mb-2">Sélectionnez une crypto pour afficher les détails.</div>

          <div class="h-[300px] sm:h-[400px]">
            <CryptoChart
              v-if="selectedCrypto"
              :series="priceSeries"
              :symbol="selectedCrypto?.symbol || 'N/A'"
              :mode="mode"
              type="area"
              height="100%"
              currency="EUR"
            />
            <div v-else class="text-sm text-gray-400">Select a crypto to view chart.</div>
            <div v-if="historyLoading" class="text-sm text-gray-400 mt-2">Loading history...</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { formatEUR } from '@/utils/format';
import CryptoChart from '@/components/CryptoChart.vue';
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

onMounted(() => {
  loadCryptos();
});

async function loadCryptos() {
  loading.value = true;
  errorMessage.value = '';
  try {
    adminCryptosLocal.value = await getAdminCryptos();
    if (adminCryptosLocal.value.length) {
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

const mode = computed(() => (selectedCrypto.value?.change24h ?? 0) >= 0 ? 'positive' : 'negative');

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
/* rely on Tailwind utilities */
</style>