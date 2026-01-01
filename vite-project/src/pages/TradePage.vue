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
              <span class="font-semibold">{{ showBalance ? formattedBalance : '****' }}</span>
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
                    <div class="w-8 h-8 sm:w-10 sm:h-10 flex-shrink-0 bg-gray-700 rounded-lg flex items-center justify-center overflow-hidden">
                      <img 
                        v-if="crypto.icon" 
                        :src="crypto.icon" 
                        :alt="crypto.name" 
                        class="w-full h-full object-contain"
                        @error="(e: any) => e.target.style.display = 'none'"
                      />
                      <span v-else class="text-white font-bold text-xs">
                        {{ crypto.symbol }}
                      </span>
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
          <!-- Professional Trading Chart -->
          <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <ProfessionalTradingChart
              :symbol="selectedCrypto?.symbol || ''"
              :crypto-name="selectedCrypto?.name || ''"
              :price-data="history"
              :current-price="selectedCrypto?.price || 0"
              :change24h="selectedCrypto?.change24h || 0"
              :crypto-icon="selectedCrypto?.icon"
              :market-cap="(selectedCrypto?.price || 0) * 21000000"
              :height="500"
              currency="EUR"
            />
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
                  <label class="block text-sm font-medium text-gray-400 mb-2">Amount (EUR)</label>
                  <input 
                    type="number" 
                    v-model="amount" 
                    placeholder="0.00" 
                    step="0.01"
                    min="0"
                    class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 sm:py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                  />
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
                    <div class="flex justify-between"><span class="text-gray-400">Price per {{ selectedCrypto?.symbol }}:</span><span>{{ formatPrice(selectedCrypto?.price || 0) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Quantity:</span><span>{{ amount ? calculateQuantity.toFixed(8) : '0.00000000' }} {{ selectedCrypto?.symbol }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Amount:</span><span>{{ formatPrice(parseFloat(amount) || 0) }}</span></div>
                    <div v-if="tradeType === 'sell' && availableQuantity !== null" class="flex justify-between text-xs"><span class="text-gray-500">Available:</span><span class="text-gray-400">{{ availableQuantity.toFixed(8) }} {{ selectedCrypto?.symbol }}</span></div>
                    <div class="border-t border-gray-600 pt-2 flex justify-between font-semibold">
                      <span>{{ tradeType === 'buy' ? 'Total Cost' : 'Total Value' }}:</span>
                      <span>{{ formatPrice(parseFloat(amount) || 0) }}</span>
                    </div>
                  </div>
                </div>

                <div class="space-y-4">
                  <button 
                    @click="handleTrade"
                    :disabled="!amount || isTrading || !canTrade"
                    class="w-full py-4 rounded-lg font-semibold transition-all transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none text-white" 
                    :style="{ backgroundColor: tradeType === 'buy' ? 'var(--accent-green)' : 'var(--accent-red)' }"
                  >
                    {{ isTrading ? 'Processing...' : `${tradeType === 'buy' ? 'Buy' : 'Sell'} ${selectedCrypto?.symbol || ''}` }}
                  </button>
                </div>
                
                <!-- Error Message -->
                <div v-if="tradeError" class="mt-4 p-3 bg-red-900/50 border border-red-700 rounded-lg">
                  <div class="flex items-center justify-between">
                    <span class="text-red-200 text-sm">{{ tradeError }}</span>
                    <button @click="tradeError = ''" class="text-red-300 hover:text-red-100">
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                </div>
                
                <!-- Success Message -->
                <div v-if="tradeSuccess" class="mt-4 p-3 bg-green-900/50 border border-green-700 rounded-lg">
                  <div class="flex items-center justify-between">
                    <span class="text-green-200 text-sm">{{ tradeSuccess }}</span>
                    <button @click="tradeSuccess = ''" class="text-green-300 hover:text-green-100">
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
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
import { ref, computed, onMounted, watch } from 'vue';
import {
  Search as SearchIcon,
  Wallet as WalletIcon,
  Shield as ShieldIcon,
  Zap as ZapIcon,
  Eye as EyeIcon,
  EyeOff as EyeOffIcon
} from 'lucide-vue-next';

import ProfessionalTradingChart from '../components/ProfessionalTradingChart.vue';
import FooterSection from '../components/sectionsLanding/FooterSection.vue';
import { adminCryptos } from '../data/cryptoData';
import { getCryptoIcon } from '../utils/cryptoIcons';
import { getMarket, getMarketHistory, buyCrypto, sellCrypto, getPortfolio } from '../services/api';
import { useAuthStore } from '@/stores/auth';
import { formatEUR } from '../utils/formatEUR';
import type { CryptoCurrency, CryptoPricePoint, PortfolioResponse, PortfolioPosition } from '../types';

type DisplayCrypto = CryptoCurrency & {
  change24h?: number;
  marketCap?: string;
  volume24h?: string;
  icon?: string;
  id?: number | string;
};

// Fallback icon map from adminCryptos for change24h and other metadata
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

const tradeType = ref<'buy' | 'sell'>('buy');
const amount = ref('');
const showBalance = ref(true);
const isTrading = ref(false);
const tradeError = ref('');
const tradeSuccess = ref('');
const portfolioData = ref<PortfolioResponse | null>(null);
const isLoadingPortfolio = ref(false);

const auth = useAuthStore();

const filteredCryptos = computed(() => {
  const q = search.value.toLowerCase().trim();
  if (!q) return cryptocurrencies.value;
  return cryptocurrencies.value.filter((c) => c.name.toLowerCase().includes(q) || c.symbol.toLowerCase().includes(q));
});

const calculateQuantity = computed(() => {
  const amountValue = parseFloat(amount.value) || 0;
  const price = selectedCrypto.value?.price || 0;
  if (!price) return 0;
  return amountValue / price;
});

const formattedBalance = computed(() => {
  const balance = auth.user?.euro_balance ?? 0;
  return formatEUR(balance);
});

// Get available quantity for selected crypto (for sell)
const availableQuantity = computed(() => {
  if (!portfolioData.value || !selectedCrypto.value) return null;
  const position = portfolioData.value.portfolio.find(
    (p: PortfolioPosition) => p.crypto?.symbol === selectedCrypto.value?.symbol
  );
  return position?.quantity ?? 0;
});

// Check if trade can be executed
const canTrade = computed(() => {
  if (!selectedCrypto.value || !amount.value) return false;
  const amountValue = parseFloat(amount.value);
  if (isNaN(amountValue) || amountValue <= 0) return false;
  
  if (tradeType.value === 'buy') {
    const balance = auth.user?.euro_balance ?? 0;
    return balance >= amountValue;
  } else {
    // For sell, check if user has enough quantity
    const quantity = calculateQuantity.value;
    return availableQuantity.value !== null && quantity > 0 && quantity <= (availableQuantity.value ?? 0);
  }
});


const selectedStyle = { backgroundColor: 'var(--blue-dark)', borderColor: 'var(--blue)', opacity: 0.2 };

// Format price in EUR
function formatPrice(value: number): string {
  return formatEUR(value);
}

async function loadPortfolio() {
  if (isLoadingPortfolio.value) return;
  isLoadingPortfolio.value = true;
  try {
    portfolioData.value = await getPortfolio();
  } catch (error) {
    console.error('Error loading portfolio:', error);
  } finally {
    isLoadingPortfolio.value = false;
  }
}

async function handleTrade() {
  if (isTrading.value || !canTrade.value || !selectedCrypto.value) return;
  
  tradeError.value = '';
  tradeSuccess.value = '';
  isTrading.value = true;
  
  try {
    const amountValue = parseFloat(amount.value);
    if (isNaN(amountValue) || amountValue <= 0) {
      tradeError.value = 'Please enter a valid amount greater than 0';
      return;
    }
    
    const price = selectedCrypto.value.price || 0;
    if (!price || price <= 0) {
      tradeError.value = 'Invalid crypto price. Please refresh the market data.';
      return;
    }
    
    const quantity = amountValue / price;
    
    if (tradeType.value === 'buy') {
      // Check balance
      const balance = auth.user?.euro_balance ?? 0;
      if (balance < amountValue) {
        tradeError.value = `Insufficient balance. Available: ${formatEUR(balance)}`;
        return;
      }
      
      // Execute buy
      const buyResponse = await buyCrypto({
        symbol: selectedCrypto.value.symbol,
        quantity: quantity
      });
      
      // Update user balance from response
      if (auth.user) {
        auth.user.euro_balance = buyResponse.balance;
        if (auth.persist) {
          auth.persist();
        }
      }
      
      tradeSuccess.value = `Successfully purchased ${quantity.toFixed(8)} ${selectedCrypto.value.symbol}`;
      amount.value = '';
      
      // Reload portfolio to update available quantities
      await loadPortfolio();
    } else {
      // Sell
      const availableQty = availableQuantity.value ?? 0;
      if (quantity > availableQty) {
        tradeError.value = `Insufficient quantity. Available: ${availableQty.toFixed(8)} ${selectedCrypto.value.symbol}`;
        return;
      }
      
      // Execute sell
      const sellResponse = await sellCrypto({
        symbol: selectedCrypto.value.symbol,
        quantity: quantity
      });
      
      // Update user balance from response
      if (auth.user) {
        auth.user.euro_balance = sellResponse.balance;
        if (auth.persist) {
          auth.persist();
        }
      }
      
      tradeSuccess.value = `Successfully sold ${quantity.toFixed(8)} ${selectedCrypto.value.symbol}`;
      amount.value = '';
      
      // Reload portfolio to update available quantities
      await loadPortfolio();
    }
    
    // Auto-hide success message after 5 seconds
    setTimeout(() => {
      tradeSuccess.value = '';
    }, 5000);
    
  } catch (error: any) {
    console.error('Trade error:', error);
    
    // Extract error message
    const errorMessage = error?.response?.data?.message || error?.message || 'An error occurred while processing your transaction';
    tradeError.value = errorMessage;
    
    // Auto-hide error message after 7 seconds
    setTimeout(() => {
      tradeError.value = '';
    }, 7000);
  } finally {
    isTrading.value = false;
  }
}

async function loadMarket() {
  loading.value = true;
  errorMessage.value = '';
  try {
    const data = await getMarket();
    cryptocurrencies.value = data.map((item) => {
      const fallback = iconMap[item.symbol];
      // Use getCryptoIcon for reliable icon mapping, fallback to adminCryptos icon if available
      const icon = getCryptoIcon(item.symbol) || fallback?.icon;
      return {
        id: item.id ?? item.symbol,
        symbol: item.symbol,
        name: item.name,
        price: item.price ?? fallback?.price ?? 0,
        change24h: fallback?.change24h ?? 0,
        marketCap: fallback?.marketCap,
        volume24h: fallback?.volume24h,
        icon: icon
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

// Watch tradeType to clear errors and amount when switching
watch(tradeType, () => {
  tradeError.value = '';
  tradeSuccess.value = '';
  amount.value = '';
});

// Watch selectedCrypto to reload portfolio
watch(() => selectedCrypto.value?.symbol, () => {
  if (selectedCrypto.value) {
    loadPortfolio();
  }
});

onMounted(async () => {
  if (!auth.user && auth.token) {
    await auth.fetchCurrentUser();
  }
  await Promise.all([loadMarket(), loadPortfolio()]);
});
</script>

<style scoped>
/* rely on Tailwind utilities */
</style>