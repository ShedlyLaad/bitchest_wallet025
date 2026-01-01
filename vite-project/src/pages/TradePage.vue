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
          <div class="bg-gradient-to-r from-gray-800 to-gray-800/95 rounded-xl p-4 sm:p-5 border border-gray-700/50 shadow-lg w-full">
            <div class="flex items-center justify-between gap-4">
              <div class="flex items-center gap-3">
                <div :class="[
                  'p-2.5 rounded-lg',
                  totalGainLoss >= 0 ? 'bg-green-500/20' : 'bg-red-500/20'
                ]">
                  <component 
                    :is="totalGainLoss >= 0 ? TrendingUpIcon : TrendingDownIcon" 
                    :class="['h-5 w-5', totalGainLoss >= 0 ? 'text-green-400' : 'text-red-400']"
                  />
                </div>
                <div>
                  <div class="text-xs text-gray-400 font-medium mb-0.5">Total P&L</div>
                  <span :class="[
                    'text-xl font-bold tracking-tight',
                    totalGainLoss >= 0 ? 'text-green-400' : 'text-red-400'
                  ]">
                    {{ showBalance ? formattedTotalGainLoss : '****' }}
                  </span>
                </div>
              </div>
              <button 
                @click="showBalance = !showBalance" 
                class="p-2 text-gray-400 hover:text-white hover:bg-gray-700/50 rounded-lg transition-all"
                title="Toggle visibility"
              >
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
          <div class="bg-gray-800 rounded-xl p-5 sm:p-6 border border-gray-700 shadow-lg">
            <!-- Header with Order Type Toggle -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
              <div>
                <h3 class="text-xl sm:text-2xl font-bold text-white">Place Order</h3>
                <p class="text-sm text-gray-400 mt-1">Trade {{ selectedCrypto?.symbol || 'crypto' }} instantly</p>
              </div>

              <div class="flex bg-gray-700/50 rounded-xl p-1 border border-gray-600/50 shadow-inner">
                <button 
                  @click="tradeType = 'buy'"
                  :class="[
                    'px-5 py-2.5 rounded-lg font-semibold text-sm transition-all duration-200',
                    tradeType === 'buy' 
                      ? 'text-white shadow-lg transform scale-105' 
                      : 'text-gray-400 hover:text-gray-300'
                  ]"
                  :style="tradeType === 'buy' ? { backgroundColor: 'var(--accent-green)' } : {}"
                >
                  Buy
                </button>
                <button 
                  @click="tradeType = 'sell'"
                  :class="[
                    'px-5 py-2.5 rounded-lg font-semibold text-sm transition-all duration-200',
                    tradeType === 'sell' 
                      ? 'text-white shadow-lg transform scale-105' 
                      : 'text-gray-400 hover:text-gray-300'
                  ]"
                  :style="tradeType === 'sell' ? { backgroundColor: 'var(--accent-red)' } : {}"
                >
                  Sell
                </button>
              </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-6">
              <!-- Left Column: Input Fields -->
              <div class="space-y-5">
                <div>
                  <label class="block text-sm font-semibold text-gray-300 mb-2.5 flex items-center gap-2">
                    <span>Amount</span>
                    <span class="text-xs font-normal text-gray-500">(EUR)</span>
                  </label>
                  <div class="relative group">
                    <input 
                      type="number" 
                      v-model="amount" 
                      @input="onAmountChange"
                      placeholder="0.00" 
                      step="0.01"
                      min="0"
                      :class="[
                        'amount-input w-full bg-gray-900/70 border-2 rounded-xl pl-4 pr-16 py-3.5 placeholder-gray-500/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 transition-all duration-200 font-semibold text-lg tracking-wide',
                        isAmountExceedingBalance ? 'text-red-400 border-red-500 focus:border-red-500' : 'text-gray-100 border-gray-600 focus:border-blue-500'
                      ]"
                    />
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-sm text-gray-400 font-semibold pointer-events-none">EUR</div>
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-semibold text-gray-300 mb-2.5 flex items-center gap-2">
                    <span>Quantity</span>
                    <span class="text-xs font-normal text-gray-500">({{ selectedCrypto?.symbol }})</span>
                  </label>
                  <div class="relative">
                    <input 
                      type="number"
                      v-model="quantity"
                      @input="onQuantityChange"
                      placeholder="0.00000000" 
                      step="0.00000001"
                      min="0"
                      class="w-full bg-gray-900/50 border-2 border-gray-700 rounded-xl pl-4 pr-20 py-3.5 text-emerald-400 placeholder-gray-500/60 font-mono text-base font-semibold tracking-wide focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30 transition-all duration-200" 
                    />
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-sm text-gray-400 font-semibold pointer-events-none">{{ selectedCrypto?.symbol }}</div>
                  </div>
                </div>

                <!-- Available Balance Info (for Sell) -->
                <div v-if="tradeType === 'sell' && availableQuantity !== null" class="bg-blue-600/10 border border-blue-600/30 rounded-lg p-3">
                  <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-400 font-medium">Available Balance</span>
                    <span class="text-blue-400 font-semibold font-mono">{{ availableQuantity.toFixed(8) }} {{ selectedCrypto?.symbol }}</span>
                  </div>
                </div>
              </div>

              <!-- Right Column: Order Summary & Action -->
              <div class="space-y-5">
                <!-- Order Summary Card -->
                <div class="bg-gradient-to-br from-gray-900/80 to-gray-800/60 rounded-xl p-5 border border-gray-700/50 shadow-inner">
                  <h4 class="font-bold text-white mb-4 text-base flex items-center gap-2">
                    <div class="w-1 h-4 rounded-full" :style="{ backgroundColor: tradeType === 'buy' ? 'var(--accent-green)' : 'var(--accent-red)' }"></div>
                    Order Summary
                  </h4>

                  <div class="space-y-3.5">
                    <div class="flex justify-between items-center py-1.5 border-b border-gray-700/50">
                      <span class="text-sm text-gray-400 font-medium">Price per {{ selectedCrypto?.symbol }}</span>
                      <span class="text-white font-semibold font-mono">{{ formatPrice(selectedCrypto?.price || 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-gray-700/50">
                      <span class="text-sm text-gray-400 font-medium">Quantity</span>
                      <span class="text-white font-semibold font-mono">{{ (parseFloat(quantity) || calculateQuantity || 0).toFixed(8) }} {{ selectedCrypto?.symbol }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-gray-700/50">
                      <span class="text-sm text-gray-400 font-medium">Amount</span>
                      <span :class="['font-semibold font-mono', isAmountExceedingBalance ? 'text-red-400' : 'text-white']">
                        {{ formatPrice(parseFloat(amount) || calculateAmount || 0) }}
                      </span>
                    </div>
                    <div class="flex justify-between items-center pt-2">
                      <span class="text-base font-bold text-gray-300">{{ tradeType === 'buy' ? 'Total Cost' : 'Total Value' }}</span>
                      <span :class="['text-lg font-bold', isAmountExceedingBalance ? 'text-red-400' : tradeType === 'buy' ? 'text-green-400' : 'text-red-400']">
                        {{ formatPrice(parseFloat(amount) || calculateAmount || 0) }}
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Action Button -->
                <button 
                  @click="handleTrade"
                  :disabled="!amount || isTrading || !canTrade"
                  :class="[
                    'w-full py-4 rounded-xl font-bold text-base transition-all duration-200 transform',
                    isTrading || (!amount || !canTrade)
                      ? 'opacity-50 cursor-not-allowed' 
                      : 'hover:scale-[1.02] hover:shadow-xl active:scale-[0.98]'
                  ]"
                  :style="{ 
                    backgroundColor: tradeType === 'buy' ? 'var(--accent-green)' : 'var(--accent-red)',
                    boxShadow: (!isTrading && amount && canTrade) ? (tradeType === 'buy' ? '0 10px 30px rgba(34, 197, 94, 0.3)' : '0 10px 30px rgba(239, 68, 68, 0.3)') : 'none'
                  }"
                >
                  <span v-if="isTrading" class="flex items-center justify-center gap-2">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                  </span>
                  <span v-else>
                    {{ tradeType === 'buy' ? 'Buy' : 'Sell' }} {{ selectedCrypto?.symbol || '' }}
                  </span>
                </button>
                
                <!-- Error Message -->
                <Transition name="slide-fade">
                  <div v-if="tradeError" class="bg-red-900/40 border-2 border-red-700/50 rounded-xl p-4 backdrop-blur-sm">
                    <div class="flex items-center justify-between gap-3">
                      <div class="flex items-center gap-2.5 flex-1">
                        <div class="w-1.5 h-1.5 rounded-full bg-red-400"></div>
                        <span class="text-red-200 text-sm font-medium flex-1">{{ tradeError }}</span>
                      </div>
                      <button @click="tradeError = ''" class="text-red-300 hover:text-red-100 transition-colors p-1 hover:bg-red-900/30 rounded">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </button>
                    </div>
                  </div>
                </Transition>
                
                <!-- Success Message -->
                <Transition name="slide-fade">
                  <div v-if="tradeSuccess" class="bg-green-900/40 border-2 border-green-700/50 rounded-xl p-4 backdrop-blur-sm">
                    <div class="flex items-center justify-between gap-3">
                      <div class="flex items-center gap-2.5 flex-1">
                        <div class="w-1.5 h-1.5 rounded-full bg-green-400"></div>
                        <span class="text-green-200 text-sm font-medium flex-1">{{ tradeSuccess }}</span>
                      </div>
                      <button @click="tradeSuccess = ''" class="text-green-300 hover:text-green-100 transition-colors p-1 hover:bg-green-900/30 rounded">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </button>
                    </div>
                  </div>
                </Transition>
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
  Eye as EyeIcon,
  EyeOff as EyeOffIcon,
  TrendingUp as TrendingUpIcon,
  TrendingDown as TrendingDownIcon
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
const quantity = ref('');
const showBalance = ref(true);
const isTrading = ref(false);
const tradeError = ref('');
const tradeSuccess = ref('');
const portfolioData = ref<PortfolioResponse | null>(null);
const isLoadingPortfolio = ref(false);
const isAmountInputActive = ref(false);
const isQuantityInputActive = ref(false);

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

const calculateAmount = computed(() => {
  const quantityValue = parseFloat(quantity.value) || 0;
  const price = selectedCrypto.value?.price || 0;
  if (!price) return 0;
  return quantityValue * price;
});

// Check if amount exceeds balance (for buy orders)
const isAmountExceedingBalance = computed(() => {
  if (tradeType.value !== 'buy') return false;
  const amountValue = parseFloat(amount.value) || 0;
  const calculatedAmount = calculateAmount.value;
  const finalAmount = amountValue || calculatedAmount;
  const balance = auth.user?.euro_balance ?? 0;
  return finalAmount > balance;
});

// Calculate total gain/loss from portfolio
const totalGainLoss = computed(() => {
  if (!portfolioData.value) return 0;
  return portfolioData.value.portfolio.reduce((sum, position) => {
    return sum + (position.gain_loss || 0);
  }, 0);
});

const formattedTotalGainLoss = computed(() => {
  const total = totalGainLoss.value;
  const sign = total >= 0 ? '+' : '';
  return `${sign}${formatEUR(Math.abs(total))}`;
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
  if (!selectedCrypto.value) return false;
  
  // Check if either amount or quantity is provided
  const amountValue = parseFloat(amount.value) || 0;
  const quantityValue = parseFloat(quantity.value) || 0;
  const finalAmount = amountValue || calculateAmount.value;
  const finalQuantity = quantityValue || calculateQuantity.value;
  
  if (finalAmount <= 0 && finalQuantity <= 0) return false;
  
  if (tradeType.value === 'buy') {
    const balance = auth.user?.euro_balance ?? 0;
    return balance >= finalAmount && finalAmount > 0;
  } else {
    // For sell, check if user has enough quantity
    return availableQuantity.value !== null && finalQuantity > 0 && finalQuantity <= (availableQuantity.value ?? 0);
  }
});

// Handle amount input change
function onAmountChange() {
  if (isQuantityInputActive.value) return;
  isAmountInputActive.value = true;
  const amountValue = parseFloat(amount.value) || 0;
  const price = selectedCrypto.value?.price || 0;
  if (price > 0 && amountValue > 0) {
    quantity.value = (amountValue / price).toFixed(8);
  } else {
    quantity.value = '';
  }
  setTimeout(() => {
    isAmountInputActive.value = false;
  }, 100);
}

// Handle quantity input change
function onQuantityChange() {
  if (isAmountInputActive.value) return;
  isQuantityInputActive.value = true;
  const quantityValue = parseFloat(quantity.value) || 0;
  const price = selectedCrypto.value?.price || 0;
  
  // For sell orders, validate against available quantity and cap it
  if (tradeType.value === 'sell' && availableQuantity.value !== null) {
    const maxQuantity = availableQuantity.value;
    if (quantityValue > maxQuantity) {
      quantity.value = maxQuantity.toFixed(8);
      const finalQuantity = maxQuantity;
      if (price > 0) {
        amount.value = (finalQuantity * price).toFixed(2);
      }
    } else if (price > 0 && quantityValue > 0) {
      amount.value = (quantityValue * price).toFixed(2);
    } else {
      amount.value = '';
    }
  } else if (price > 0 && quantityValue > 0) {
    amount.value = (quantityValue * price).toFixed(2);
  } else {
    amount.value = '';
  }
  
  setTimeout(() => {
    isQuantityInputActive.value = false;
  }, 100);
}


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
    const price = selectedCrypto.value.price || 0;
    if (!price || price <= 0) {
      tradeError.value = 'Invalid crypto price. Please refresh the market data.';
      return;
    }
    
    // Get final quantity (either from quantity input or calculated from amount)
    const quantityValue = parseFloat(quantity.value) || 0;
    const amountValue = parseFloat(amount.value) || 0;
    const finalQuantity = quantityValue || (amountValue / price);
    const finalAmount = amountValue || (quantityValue * price);
    
    if (finalQuantity <= 0) {
      tradeError.value = 'Please enter a valid quantity greater than 0';
      return;
    }
    
    if (tradeType.value === 'buy') {
      // Check balance
      const balance = auth.user?.euro_balance ?? 0;
      if (balance < finalAmount) {
        tradeError.value = `Insufficient balance. Available: ${formatEUR(balance)}`;
        return;
      }
      
      // Execute buy
      const buyResponse = await buyCrypto({
        symbol: selectedCrypto.value.symbol,
        quantity: finalQuantity
      });
      
      // Update user balance from response
      if (auth.user) {
        auth.user.euro_balance = buyResponse.balance;
        if (auth.persist) {
          auth.persist();
        }
      }
      
      tradeSuccess.value = `Successfully purchased ${finalQuantity.toFixed(8)} ${selectedCrypto.value.symbol}`;
      amount.value = '';
      quantity.value = '';
      
      // Reload portfolio to update available quantities
      await loadPortfolio();
    } else {
      // Sell - Validate quantity against portfolio
      const availableQty = availableQuantity.value ?? 0;
      if (availableQty <= 0) {
        tradeError.value = `You don't have any ${selectedCrypto.value.symbol} in your portfolio`;
        return;
      }
      
      if (finalQuantity > availableQty) {
        tradeError.value = `Insufficient quantity. You have ${availableQty.toFixed(8)} ${selectedCrypto.value.symbol} available`;
        return;
      }
      
      if (finalQuantity <= 0) {
        tradeError.value = 'Please enter a valid quantity greater than 0';
        return;
      }
      
      // Execute sell
      const sellResponse = await sellCrypto({
        symbol: selectedCrypto.value.symbol,
        quantity: finalQuantity
      });
      
      // Update user balance from response
      if (auth.user) {
        auth.user.euro_balance = sellResponse.balance;
        if (auth.persist) {
          auth.persist();
        }
      }
      
      tradeSuccess.value = `Successfully sold ${finalQuantity.toFixed(8)} ${selectedCrypto.value.symbol}`;
      amount.value = '';
      quantity.value = '';
      
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
  quantity.value = '';
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

/* Slide Fade Transition for Messages */
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.2s ease-in;
}

.slide-fade-enter-from {
  transform: translateY(-10px);
  opacity: 0;
}

.slide-fade-leave-to {
  transform: translateY(-10px);
  opacity: 0;
}

/* Styling for number input spinners (arrows) - Gray color instead of white */
.amount-input::-webkit-inner-spin-button,
.amount-input::-webkit-outer-spin-button {
  opacity: 1;
  cursor: pointer;
  filter: grayscale(1) brightness(0.7);
}

/* Firefox - hide default arrows */
.amount-input[type="number"] {
  -moz-appearance: textfield;
  appearance: textfield;
}
</style>