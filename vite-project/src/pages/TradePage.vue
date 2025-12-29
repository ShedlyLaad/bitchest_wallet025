<template>
  <div class="min-h-screen bg-gray-900 text-white">
    <div class="max-w-7xl mx-auto p-3 sm:p-6 space-y-4 sm:space-y-8">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold">Trade Cryptocurrencies</h1>
          <p class="text-gray-400 mt-1 text-sm sm:text-base">Buy and sell crypto with competitive rates</p>
        </div>

        <div class="w-full sm:w-auto">
          <div class="bg-gray-800 rounded-lg p-3 sm:p-4 border border-gray-700 w-full">
            <div class="flex items-center justify-between sm:space-x-2">
              <WalletIcon class="h-5 w-5" />
              <span class="text-sm text-gray-400">Balance:</span>
              <span class="font-semibold">{{ showBalance ? walletBalanceFormatted : '****' }}</span>
              <button @click="showBalance = !showBalance" class="text-gray-400 hover:text-white">
                <component :is="showBalance ? EyeOffIcon : EyeIcon" class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="grid lg:grid-cols-3 gap-4 sm:gap-8">
        <!-- Crypto List -->
        <div class="lg:col-span-1 order-2 lg:order-1">
          <div class="bg-gray-800 rounded-xl border border-gray-700">
            <div class="p-4 sm:p-6 border-b border-gray-700">
              <div class="relative">
                <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input
                  v-model="search"
                  type="text"
                  placeholder="Search cryptocurrencies..."
                  class="w-full bg-gray-700 border border-gray-600 rounded-lg pl-10 pr-4 py-2 sm:py-3 text-sm sm:text-base text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div v-if="errorMessage" class="text-xs text-red-400 mt-2">{{ errorMessage }}</div>
              <div v-else-if="loading" class="text-xs text-gray-400 mt-2">Loading market data...</div>
            </div>

            <div class="divide-y divide-gray-700 max-h-[300px] sm:max-h-[500px] overflow-y-auto">
              <button
                v-for="crypto in filteredCryptos"
                :key="crypto.id"
                @click="selectCrypto(crypto)"
                :class="['w-full p-4 text-left hover:bg-gray-700/50 transition-colors', selectedCrypto?.id === crypto.id ? 'border-r-2' : '']"
                :style="selectedCrypto?.id === crypto.id ? selectedStyle : {}"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 flex-shrink-0">
                      <img :src="crypto.icon" :alt="crypto.name" class="w-full h-full object-contain" />
                    </div>
                    <div>
                      <div class="font-semibold">{{ crypto.symbol }}</div>
                      <div class="text-sm text-gray-400">{{ crypto.name }}</div>
                    </div>
                  </div>

                  <div class="text-right">
                    <div class="font-semibold">${{ (crypto.price || 0).toLocaleString() }}</div>
                    <div :class="(crypto.change24h ?? 0) >= 0 ? 'PnL--pos text-sm' : 'PnL--neg text-sm'">
                      {{ (crypto.change24h ?? 0) >= 0 ? '+' : '' }}{{ crypto.change24h ?? 0 }}%
                    </div>
                  </div>
                </div>
              </button>
            </div>
          </div>
        </div>

        <!-- Trading Interface -->
        <div class="lg:col-span-2 space-y-4 sm:space-y-8 order-1 lg:order-2">
          <!-- Chart -->
          <div class="bg-gray-800 rounded-xl p-4 sm:p-6 border border-gray-700">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
              <div class="flex items-center space-x-4">
                <div class="w-10 h-10 sm:w-12 sm:h-12 flex-shrink-0">
                  <img :src="selectedCrypto?.icon" :alt="selectedCrypto?.name" class="w-full h-full object-contain" />
                </div>
                <div>
                  <h2 class="text-xl sm:text-2xl font-bold">{{ selectedCrypto?.name }}</h2>
                  <div class="flex flex-wrap items-center gap-4 text-sm text-gray-400">
                    <span>${{ (selectedCrypto?.price || 0).toLocaleString() }}</span>
                    <span :class="(selectedCrypto?.change24h ?? 0) >= 0 ? 'PnL--pos' : 'PnL--neg'">
                      <component :is="(selectedCrypto?.change24h ?? 0) >= 0 ? TrendingUpIcon : TrendingDownIcon" class="h-4 w-4 inline" />
                      {{ (selectedCrypto?.change24h ?? 0) >= 0 ? '+' : '' }}{{ selectedCrypto?.change24h ?? 0 }}%
                    </span>
                  </div>
                </div>
              </div>
              <button class="text-gray-400 hover:text-yellow-400"><StarIcon class="h-5 w-5" /></button>
            </div>

            <div class="h-[250px] sm:h-[350px]">
              <CryptoChart
                :series="chartSeries"
                :symbol="selectedCrypto?.symbol"
                :mode="(selectedCrypto?.change24h ?? 0) >= 0 ? 'positive' : 'negative'"
                height="100%"
                :showGrid="true"
                :animated="true"
              />
            </div>
          </div>

          <!-- Trade Form -->
          <div class="bg-gray-800 rounded-xl p-4 sm:p-6 border border-gray-700">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-xl font-semibold">Place Order</h3>

              <div class="flex bg-gray-700 rounded-lg p-1">
                <button @click="tradeType = 'buy'" :class="tradeType === 'buy' ? 'text-white px-4 py-2 rounded-md' : 'text-gray-400 hover:text-white px-4 py-2 rounded-md'" :style="tradeType === 'buy' ? { backgroundColor: 'var(--accent-green)' } : {}">Buy</button>
                <button @click="tradeType = 'sell'" :class="tradeType === 'sell' ? 'text-white px-4 py-2 rounded-md' : 'text-gray-400 hover:text-white px-4 py-2 rounded-md'" :style="tradeType === 'sell' ? { backgroundColor: 'var(--accent-red)' } : {}">Sell</button>
              </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4 sm:gap-6">
              <div class="space-y-4 sm:space-y-6">
                <div>
                  <label class="block text-sm font-medium text-gray-400 mb-2">Fiat Currency</label>
                  <select v-model="selectedFiat" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 sm:py-3 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option v-for="f in fiatCurrencies" :key="f" :value="f">{{ f }}</option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-400 mb-2">Amount ({{ selectedFiat }})</label>
                  <input type="number" v-model="amount" placeholder="0.00" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 sm:py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-400 mb-2">Quantity ({{ selectedCrypto?.symbol }})</label>
                  <input type="number" :value="amount ? calculateQuantity.toFixed(8) : ''" readonly placeholder="0.00000000" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 sm:py-3 text-white placeholder-gray-400 opacity-60" />
                </div>
              </div>

              <div class="space-y-4 sm:space-y-6">
                <div class="bg-gray-700/30 rounded-lg p-4 sm:p-6 space-y-3">
                  <h4 class="font-semibold text-gray-300">Order Summary</h4>

                  <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-400">Price per {{ selectedCrypto?.symbol }}:</span><span>${{ (selectedCrypto?.price || 0).toLocaleString() }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Amount:</span><span>${{ amount || '0' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Fee ({{ fee }}%):</span><span>${{ amount ? ((parseFloat(amount) * fee) / 100).toFixed(2) : '0.00' }}</span></div>
                    <div class="border-t border-gray-600 pt-2 flex justify-between font-semibold"><span>Total:</span><span>${{ amount ? calculateTotal.toFixed(2) : '0.00' }}</span></div>
                  </div>
                </div>

                <div class="space-y-4">
                  <button :disabled="!amount" class="w-full py-4 rounded-lg font-semibold transition-all transform hover:scale-105 text-white" :style="{ backgroundColor: tradeType === 'buy' ? 'var(--accent-green)' : 'var(--accent-red)' }">{{ tradeType === 'buy' ? 'Buy' : 'Sell' }} {{ selectedCrypto.symbol }}</button>
                  <button class="w-full py-3 border border-gray-600 rounded-lg text-gray-300 hover:bg-gray-700/50 transition-colors">Preview Transaction</button>
                </div>
              </div>
            </div>

            <!-- Wallet Integration -->
            <div class="mt-6 sm:mt-8 p-3 sm:p-4 bg-blue-600/10 border border-blue-600/30 rounded-lg">
              <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center space-x-3"><ShieldIcon class="h-5 w-5 text-blue-400" /><span class="font-medium text-sm sm:text-base">Secure Wallet Integration</span></div>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                  <button class="flex-1 sm:flex-none bg-orange-600 hover:bg-orange-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">MetaMask</button>
                  <button class="flex-1 sm:flex-none bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">WalletConnect</button>
                </div>
              </div>
            </div>

            <!-- 2FA Notice -->
            <div class="mt-4 p-3 sm:p-4 bg-yellow-600/10 border border-yellow-600/30 rounded-lg">
              <div class="flex items-start sm:items-center space-x-3">
                <ZapIcon class="h-5 w-5 text-yellow-400 mt-1 sm:mt-0" />
                <div><div class="font-medium text-yellow-400">2FA Required</div><div class="text-sm text-gray-300">Two-factor authentication will be required to complete this transaction</div></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer - Full Width -->
    <FooterSection />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import {
  TrendingUp as TrendingUpIcon,
  TrendingDown as TrendingDownIcon,
  Search as SearchIcon,
  Star as StarIcon,
  Wallet as WalletIcon,
  Shield as ShieldIcon,
  Zap as ZapIcon,
  Eye as EyeIcon,
  EyeOff as EyeOffIcon
} from 'lucide-vue-next';

import CryptoChart from '../components/CryptoChart.vue';
import FooterSection from '../components/sectionsLanding/FooterSection.vue';
import { adminCryptos } from '../data/cryptoData';
import { getMarket, getMarketHistory } from '../services/api';
import type { CryptoCurrency, CryptoPricePoint } from '../types';

type DisplayCrypto = CryptoCurrency & {
  change24h?: number;
  marketCap?: string;
  volume24h?: string;
  icon?: string;
  id?: number | string;
};

const iconMap = adminCryptos.reduce<Record<string, (typeof adminCryptos)[number]>>((acc, c) => {
  acc[c.symbol] = c;
  return acc;
}, {});

const cryptocurrencies = ref<DisplayCrypto[]>([...adminCryptos]);
const selectedCrypto = ref<DisplayCrypto>(cryptocurrencies.value[0]);
const history = ref<number[]>([]);
const search = ref('');
const loading = ref(false);
const historyLoading = ref(false);
const errorMessage = ref('');

const selectedFiat = ref('USD');
const tradeType = ref<'buy' | 'sell'>('buy');
const amount = ref('');
const showBalance = ref(true);

const fiatCurrencies = ['USD', 'EUR', 'GBP', 'JPY', 'CAD'];
const walletBalance = 25000;
const fee = 0.1;

const filteredCryptos = computed(() => {
  const q = search.value.toLowerCase().trim();
  if (!q) return cryptocurrencies.value;
  return cryptocurrencies.value.filter((c) => c.name.toLowerCase().includes(q) || c.symbol.toLowerCase().includes(q));
});

const calculateTotal = computed(() => {
  const amountValue = parseFloat(amount.value) || 0;
  const feeAmount = amountValue * (fee / 100);
  return amountValue + feeAmount;
});

const calculateQuantity = computed(() => {
  const amountValue = parseFloat(amount.value) || 0;
  const price = selectedCrypto.value?.price || 0;
  if (!price) return 0;
  return amountValue / price;
});

const chartSeries = computed(() => {
  if (history.value.length) return history.value;
  const fallbackPrice = selectedCrypto.value?.price || 0;
  return Array.from({ length: 30 }, () => fallbackPrice);
});

const walletBalanceFormatted = computed(() => `$${walletBalance.toLocaleString()}`);

const selectedStyle = { backgroundColor: 'var(--blue-dark)', borderColor: 'var(--blue)', opacity: 0.2 };

async function loadMarket() {
  loading.value = true;
  errorMessage.value = '';
  try {
    const data = await getMarket();
    cryptocurrencies.value = data.map((item) => {
      const fallback = iconMap[item.symbol];
      return {
        id: item.id ?? item.symbol,
        symbol: item.symbol,
        name: item.name,
        price: item.price ?? fallback?.price ?? 0,
        change24h: fallback?.change24h ?? 0,
        marketCap: fallback?.marketCap,
        volume24h: fallback?.volume24h,
        icon: fallback?.icon
      };
    });

    if (!cryptocurrencies.value.length) {
      cryptocurrencies.value = [...adminCryptos];
    }

    selectedCrypto.value = cryptocurrencies.value[0];
    if (selectedCrypto.value) {
      await loadHistory(selectedCrypto.value.symbol);
    }
  } catch (e: any) {
    errorMessage.value = e?.response?.data?.message || 'Impossible de charger le marché';
    cryptocurrencies.value = [...adminCryptos];
    selectedCrypto.value = cryptocurrencies.value[0];
    history.value = [];
  } finally {
    loading.value = false;
  }
}

async function loadHistory(symbol: string) {
  historyLoading.value = true;
  try {
    const data = await getMarketHistory(symbol);
    history.value = (data as CryptoPricePoint[]).map((p) => Number(p.price));
  } catch {
    history.value = [];
  } finally {
    historyLoading.value = false;
  }
}

const selectCrypto = async (crypto: DisplayCrypto) => {
  selectedCrypto.value = crypto;
  await loadHistory(crypto.symbol);
};

onMounted(() => {
  loadMarket();
});
</script>

<style scoped>
/* rely on Tailwind utilities */
</style>