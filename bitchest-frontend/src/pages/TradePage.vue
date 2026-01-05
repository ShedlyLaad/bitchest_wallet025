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
          <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl p-4 sm:p-5 border border-gray-700/50 shadow-lg hover:shadow-xl transition-all duration-300 w-full group relative overflow-hidden">
            <!-- Animated background gradient on hover -->
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/0 via-blue-500/0 to-blue-500/0 group-hover:from-blue-500/5 group-hover:via-blue-500/3 group-hover:to-transparent transition-all duration-500"></div>
            
            <div class="relative flex items-center justify-between gap-4">
              <div class="flex items-center gap-3">
                <div 
                  class="p-3 rounded-xl transition-all duration-500 group-hover:scale-110 group-hover:rotate-6"
                  :style="{ 
                    backgroundColor: totalGainLoss >= 0 ? 'rgba(1, 255, 25, 0.2)' : 'rgba(255, 89, 100, 0.2)',
                    border: `1px solid ${totalGainLoss >= 0 ? 'rgba(1, 255, 25, 0.4)' : 'rgba(255, 89, 100, 0.4)'}`
                  }"
                >
                  <component 
                    :is="totalGainLoss >= 0 ? TrendingUpIcon : TrendingDownIcon" 
                    class="h-6 w-6"
                    :style="{ color: totalGainLoss >= 0 ? '#01ff19' : '#ff5964' }"
                  />
                </div>
                <div>
                  <div class="text-xs text-gray-400 font-medium mb-0.5">Total P&L</div>
                  <span 
                    class="text-2xl sm:text-3xl font-bold tracking-tight transition-all duration-300 group-hover:scale-105" 
                    :style="{ color: totalGainLoss >= 0 ? '#01ff19' : '#ff5964' }"
                  >
                    {{ showBalance ? formattedTotalGainLoss : '****' }}
                  </span>
                </div>
              </div>
            </div>
            
            <!-- Decorative element -->
            <div class="absolute bottom-0 right-0 w-24 h-24 opacity-5 group-hover:opacity-10 transition-opacity duration-500">
              <component 
                :is="totalGainLoss >= 0 ? TrendingUpIcon : TrendingDownIcon" 
                class="w-full h-full"
                :style="{ color: totalGainLoss >= 0 ? '#01ff19' : '#ff5964' }"
              />
            </div>
          </div>
        </div>
      </div>

      <div class="grid lg:grid-cols-3 gap-4 sm:gap-8">
        <!-- Crypto List -->
        <div class="lg:col-span-1 order-2 lg:order-1">
          <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl border border-gray-700 shadow-lg overflow-hidden">
            <div class="p-4 border-b border-gray-700 bg-gray-800/50">
              <div class="flex items-center justify-between mb-3">
                <div>
                  <h3 class="text-lg font-semibold text-white">Cryptocurrencies</h3>
                  <p class="text-xs text-gray-400 mt-1">{{ filteredCryptos.length }} assets</p>
                </div>
                <div class="px-2 py-1 bg-green-500/20 border border-green-500/30 rounded-lg">
                  <span class="text-xs font-medium text-green-400">Live</span>
                </div>
              </div>
              <div class="relative">
                <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input
                  v-model="search"
                  type="text"
                  placeholder="Search cryptocurrencies..."
                  class="w-full bg-gray-700/50 border border-gray-600/50 rounded-lg pl-10 pr-4 py-2.5 text-sm text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all"
                />
              </div>
              <!-- Error message hidden to avoid display issues -->
              <div v-if="errorMessage" class="hidden">{{ errorMessage }}</div>
            </div>

            <div class="overflow-x-auto max-h-[300px] sm:max-h-[500px] overflow-y-auto crypto-table-container">
              <div class="p-2">
                <div
                  v-for="crypto in filteredCryptos"
                  :key="`${crypto.symbol}-${crypto.price}-${crypto.change24h}`"
                  @click="selectCrypto(crypto)"
                  :class="[
                    'group relative mb-2 rounded-lg border transition-all duration-300 cursor-pointer overflow-hidden',
                    selectedCrypto?.symbol === crypto.symbol
                      ? 'bg-gradient-to-r from-blue-600/20 to-blue-500/10 border-blue-500/50 shadow-lg shadow-blue-500/20 scale-[1.02]'
                      : 'bg-gray-800/30 border-gray-700/50 hover:scale-105 hover:bg-gray-700/40 hover:border-blue-500/30 hover:shadow-xl hover:shadow-blue-500/10'
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
                          selectedCrypto?.symbol === crypto.symbol
                            ? 'border-blue-500/50 bg-blue-500/10 scale-110'
                            : 'border-gray-600/50 bg-gray-700/50 group-hover:border-blue-400/50 group-hover:scale-110 group-hover:bg-blue-500/10',
                          (crypto as any).isUpdating && (crypto as any).animationClass === 'price-up' ? 'animate-pulse-price-up' : '',
                          (crypto as any).isUpdating && (crypto as any).animationClass === 'price-down' ? 'animate-pulse-price-down' : ''
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
                        <div v-if="selectedCrypto?.symbol === crypto.symbol" class="absolute -top-1 -right-1 w-3 h-3 bg-blue-500 rounded-full border-2 border-gray-900 animate-pulse"></div>
                      </div>
                      
                      <!-- Crypto Details -->
                      <div class="flex flex-col min-w-0 flex-1">
                        <div class="flex items-center gap-2 mb-0.5">
                          <span class="text-white font-bold text-base group-hover:text-blue-400 transition-colors">{{ crypto.symbol }}</span>
                          <span v-if="selectedCrypto?.symbol === crypto.symbol" class="px-1.5 py-0.5 bg-blue-500/20 text-blue-400 text-xs font-medium rounded">Active</span>
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
                            (crypto as any).isUpdating && (crypto as any).animationClass === 'price-up' ? 'text-green-400 animate-pulse' : '',
                            (crypto as any).isUpdating && (crypto as any).animationClass === 'price-down' ? 'text-red-400 animate-pulse' : ''
                          ]"
                        >
                          {{ formatEUR(crypto.price ?? 0) }}
                          <span 
                            v-if="(crypto as any).previousPrice && crypto.price && Number(crypto.price) > Number((crypto as any).previousPrice)"
                            class="ml-1 text-green-400 text-xs animate-fade-in"
                          >
                            ↑
                          </span>
                          <span 
                            v-else-if="(crypto as any).previousPrice && crypto.price && Number(crypto.price) < Number((crypto as any).previousPrice)"
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
                            (crypto as any).isUpdating && (crypto as any).animationClass === 'change-up' ? 'animate-pulse-change scale-110' : ''
                          ]"
                        >
                          <span>{{ (crypto.change24h ?? 0) >= 0 ? '+' : '' }}{{ (crypto.change24h ?? 0).toFixed(2) }}%</span>
                          <span 
                            v-if="(crypto as any).previousChange24h !== undefined && crypto.change24h !== undefined && 
                                  Math.abs(Number(crypto.change24h)) > Math.abs(Number((crypto as any).previousChange24h))"
                            :class="[
                              'absolute -top-1 -right-1 w-2 h-2 rounded-full animate-ping',
                              Number(crypto.change24h) >= 0 ? 'bg-green-400' : 'bg-red-400'
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

        <!-- Trading Interface -->
        <div class="lg:col-span-2 space-y-4 sm:space-y-8 order-1 lg:order-2">
          <!-- Professional Trading Chart -->
          <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <ProfessionalTradingChart
              :symbol="selectedCrypto?.symbol || ''"
              :crypto-name="selectedCrypto?.name || ''"
              :price-data-with-dates="history"
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
                  :style="tradeType === 'buy' ? { backgroundColor: '#01ff19' } : {}"
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
                  :style="tradeType === 'sell' ? { backgroundColor: '#ff5964' } : {}"
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
                        isAmountExceedingBalance ? 'border-red-500 focus:border-red-500' : 'text-gray-100 border-gray-600 focus:border-blue-500'
                      ]"
                      :style="isAmountExceedingBalance ? { color: '#ff5964' } : {}"
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
                    <div class="w-1 h-4 rounded-full" :style="{ backgroundColor: tradeType === 'buy' ? '#01ff19' : '#ff5964' }"></div>
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
                      <span class="font-semibold font-mono" :style="{ color: isAmountExceedingBalance ? '#ff5964' : '#fff' }">
                        {{ formatPrice(parseFloat(amount) || calculateAmount || 0) }}
                      </span>
                    </div>
                    <div class="flex justify-between items-center pt-2">
                      <span class="text-base font-bold text-gray-300">{{ tradeType === 'buy' ? 'Total Cost' : 'Total Value' }}</span>
                      <span class="text-lg font-bold" :style="{ color: isAmountExceedingBalance ? '#ff5964' : tradeType === 'buy' ? '#01ff19' : '#ff5964' }">
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
                    backgroundColor: tradeType === 'buy' ? '#01ff19' : '#ff5964',
                    boxShadow: (!isTrading && amount && canTrade) ? (tradeType === 'buy' ? '0 10px 30px rgba(1, 255, 25, 0.3)' : '0 10px 30px rgba(255, 89, 100, 0.3)') : 'none'
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
                  <div v-if="tradeError" class="border-2 rounded-xl p-4 backdrop-blur-sm" style="background-color: rgba(255, 89, 100, 0.15); border-color: rgba(255, 89, 100, 0.5);">
                    <div class="flex items-center justify-between gap-3">
                      <div class="flex items-center gap-2.5 flex-1">
                        <div class="w-1.5 h-1.5 rounded-full" style="background-color: #ff5964;"></div>
                        <span class="text-sm font-medium flex-1" style="color: rgba(255, 89, 100, 0.9);">{{ tradeError }}</span>
                      </div>
                      <button @click="tradeError = ''" class="transition-colors p-1 rounded" style="color: rgba(255, 89, 100, 0.8);">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </button>
                    </div>
                  </div>
                </Transition>
                
                <!-- Success Message -->
                <Transition name="slide-fade">
                  <div v-if="tradeSuccess" class="border-2 rounded-xl p-4 backdrop-blur-sm" style="background-color: rgba(1, 255, 25, 0.15); border-color: rgba(1, 255, 25, 0.5);">
                    <div class="flex items-center justify-between gap-3">
                      <div class="flex items-center gap-2.5 flex-1">
                        <div class="w-1.5 h-1.5 rounded-full" style="background-color: #01ff19;"></div>
                        <span class="text-sm font-medium flex-1" style="color: rgba(1, 255, 25, 0.9);">{{ tradeSuccess }}</span>
                      </div>
                      <button @click="tradeSuccess = ''" class="transition-colors p-1 rounded" style="color: rgba(1, 255, 25, 0.8);">
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

    <!-- Footer - Minimal -->
    <UserFooter />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import {
  Search as SearchIcon,
  TrendingUp as TrendingUpIcon,
  TrendingDown as TrendingDownIcon
} from 'lucide-vue-next';

import ProfessionalTradingChart from '../components/ProfessionalTradingChart.vue';
import UserFooter from '../components/UserFooter.vue';
// Ne plus utiliser adminCryptos - uniquement les données de l'API
import { getCryptoIcon } from '../utils/cryptoIcons';
import { getMarketHistory, buyCrypto, sellCrypto, getPortfolio, getUserCryptos } from '../services/api';
import { useAuthStore } from '@/stores/auth';
import { formatEUR } from '../utils/formatEUR';
import type { CryptoCurrency, CryptoPricePoint, PortfolioResponse, PortfolioPosition } from '../types';

type DisplayCrypto = CryptoCurrency & {
  change24h?: number;
  marketCap?: string | number;
  volume24h?: string | number;
  icon?: string;
  id?: number | string;
  previousPrice?: number;
  previousChange24h?: number;
  isUpdating?: boolean;
  animationClass?: string;
};

// Ne pas initialiser avec adminCryptos - utiliser uniquement les données de l'API (comme AdminMarket)
const cryptocurrencies = ref<DisplayCrypto[]>([]);
const selectedCrypto = ref<DisplayCrypto | null>(null);
const history = ref<CryptoPricePoint[]>([]);
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

// Calculer le gain/perte total depuis le portfolio
// Le backend utilise Coinbase API pour obtenir les prix réels et calculer le P&L
const totalGainLoss = computed(() => {
  if (!portfolioData.value || !portfolioData.value.portfolio) return 0;
  
  return portfolioData.value.portfolio.reduce((sum, position) => {
    // Utiliser gain_loss du backend (calculé avec prix Coinbase API)
    if (position.gain_loss !== undefined && position.gain_loss !== null) {
      return sum + Number(position.gain_loss);
    }
    
    // Fallback uniquement si le backend ne fournit pas gain_loss
    // Utiliser current_value et total_invested_value du backend
    const currentValue = Number(position.current_value) || 0;
    const totalInvestedValue = Number(position.total_invested_value) || Number(position.invested_value) || 0;
    const calculatedGainLoss = currentValue - totalInvestedValue;
    
    return sum + calculatedGainLoss;
  }, 0);
});

const formattedTotalGainLoss = computed(() => {
  const total = totalGainLoss.value;
  const sign = total >= 0 ? '+' : '';
  // Format with 2 decimal places for consistency
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



// Format price in EUR (same as AdminMarket)
function formatPrice(value: number): string {
  if (!value || isNaN(value) || value <= 0) return formatEUR(0);
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
      
      // Reload portfolio to update available quantities and profit/loss (P&L dynamique)
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
      
      // Reload portfolio to update available quantities and profit/loss (P&L dynamique)
      await loadPortfolio();
      
      // Recharger aussi le marché pour mettre à jour les prix en temps réel
      await loadMarket();
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

// Store previous prices for animation (used in loadMarket)

async function loadMarket() {
  if (loading.value) return; // Prevent concurrent calls
  loading.value = true;
  errorMessage.value = '';
  try {
    // Récupérer les données depuis Coinbase API via le backend
    // Le backend utilise CoinbaseAPIService pour obtenir les prix réels et dynamiques
    const data = await getUserCryptos();
    
    // Ensure data is an array
    if (!Array.isArray(data)) {
      throw new Error('Invalid response format from API');
    }
    
    // Stocker les prix précédents pour détecter les changements (EXACTEMENT la même logique qu'AdminMarket ligne 234-236)
    const previousPrices = new Map(
      cryptocurrencies.value.map(c => [c.symbol, { price: c.price, change24h: c.change24h }])
    );
    
    // Utiliser EXACTEMENT la même logique qu'AdminMarket (ligne 238-266) pour garantir l'identité des valeurs
    cryptocurrencies.value = data.map((crypto: any) => {
      const prev = previousPrices.get(crypto.symbol);
      
      // Utiliser les données réelles de Coinbase API (prix et change24h)
      // Le backend calcule change24h depuis l'historique local si Coinbase ne le fournit pas
      const currentPrice = crypto.price !== undefined && crypto.price !== null && !isNaN(crypto.price) && crypto.price > 0
        ? Number(crypto.price)
        : 0;
      
      const currentChange24h = crypto.change24h !== undefined && crypto.change24h !== null && !isNaN(crypto.change24h)
        ? Number(crypto.change24h)
        : 0;
      
      
      // Détecter les changements pour animation uniquement si on a des prix précédents valides
      const isPriceIncreased = prev && currentPrice > 0 && prev.price && currentPrice > prev.price;
      const isPriceDecreased = prev && currentPrice > 0 && prev.price && currentPrice < prev.price;
      const isChangeIncreased = prev && currentChange24h !== undefined && prev.change24h !== undefined && 
                                Math.abs(currentChange24h) > Math.abs(prev.change24h ?? 0);
      
      // Retourner EXACTEMENT le même format qu'AdminMarket (ligne 256-265)
      return {
        ...crypto,
        price: currentPrice,
        change24h: currentChange24h,
        icon: getCryptoIcon(crypto.symbol),
        previousPrice: prev?.price,
        previousChange24h: prev?.change24h,
        isUpdating: false,
        animationClass: isPriceIncreased ? 'price-up' : isPriceDecreased ? 'price-down' : isChangeIncreased ? 'change-up' : '',
      };
    });
    
    // Animer les changements (EXACTEMENT la même logique qu'AdminMarket ligne 268-277)
    cryptocurrencies.value.forEach((crypto) => {
      if (crypto.animationClass) {
        crypto.isUpdating = true;
        setTimeout(() => {
          crypto.isUpdating = false;
          crypto.animationClass = '';
        }, 1500);
      }
    });
    
    // Trier par change24h - trier d'abord par valeur positive décroissante, puis par valeur négative croissante
    // Cela évite que les valeurs négatives extrêmes (ex: -50%) apparaissent en haut
    cryptocurrencies.value.sort((a, b) => {
      const changeA = a.change24h ?? 0;
      const changeB = b.change24h ?? 0;
      
      // Si les deux sont positifs ou les deux sont négatifs, trier par valeur absolue décroissante
      if ((changeA >= 0 && changeB >= 0) || (changeA < 0 && changeB < 0)) {
      return Math.abs(changeB) - Math.abs(changeA);
      }
      // Les valeurs positives passent avant les négatives
      return changeB >= 0 ? 1 : -1;
    });
    
    // Mettre à jour la crypto sélectionnée si elle existe toujours (EXACTEMENT la même logique qu'AdminMarket ligne 288-299)
    if (selectedCrypto.value) {
      const updated = cryptocurrencies.value.find(c => c.symbol === selectedCrypto.value?.symbol);
      if (updated) {
        selectedCrypto.value = updated;
      }
    }
    
    if (cryptocurrencies.value.length && !selectedCrypto.value) {
      selectedCrypto.value = cryptocurrencies.value[0];
      await loadHistory(selectedCrypto.value.symbol);
    }
  } catch (e: any) {
    console.error('Error loading market:', e);
    errorMessage.value = e?.response?.data?.message || e?.message || 'Impossible de charger le marché';
    // Ne PAS utiliser de données mockées comme fallback - garder le tableau vide si l'API échoue
    // Les données affichées proviennent directement de Coinbase API via le backend
    cryptocurrencies.value = [];
    selectedCrypto.value = null;
    history.value = [];
  } finally {
    loading.value = false;
  }
}

async function loadHistory(symbol: string) {
  historyLoading.value = true;
  try {
    const data = await getMarketHistory(symbol);
    history.value = data as CryptoPricePoint[];
  } catch {
    history.value = [];
  } finally {
    historyLoading.value = false;
  }
}

const selectCrypto = async (crypto: DisplayCrypto) => {
  // Use symbol for matching to avoid issues with ID changes
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

// Auto-refresh market data (EXACTEMENT la même logique qu'AdminMarket - refresh toutes les 24h)
// Timer pour rafraîchir les données depuis Coinbase API
let refreshTimer: ReturnType<typeof setInterval> | null = null;
const REFRESH_INTERVAL = 86400000; // Rafraîchir toutes les 24h (86400000 ms)

onMounted(async () => {
  if (!auth.user && auth.token) {
    await auth.fetchCurrentUser();
  }
  
  // Charger les données initiales
  await Promise.all([loadMarket(), loadPortfolio()]);
  
  // Rafraîchir automatiquement les prix depuis Coinbase API toutes les 24h
  refreshTimer = setInterval(() => {
    loadMarket();
  }, REFRESH_INTERVAL);
});

onUnmounted(() => {
  if (refreshTimer) {
    clearInterval(refreshTimer);
    refreshTimer = null;
  }
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

/* Custom scrollbar for crypto table */
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

@keyframes pulse-price-up {
  0%, 100% {
    background-color: rgba(34, 197, 94, 0.1);
    border-color: rgba(34, 197, 94, 0.3);
  }
  50% {
    background-color: rgba(34, 197, 94, 0.2);
    border-color: rgba(34, 197, 94, 0.5);
  }
}

@keyframes pulse-price-down {
  0%, 100% {
    background-color: rgba(239, 68, 68, 0.1);
    border-color: rgba(239, 68, 68, 0.3);
  }
  50% {
    background-color: rgba(239, 68, 68, 0.2);
    border-color: rgba(239, 68, 68, 0.5);
  }
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

.animate-pulse-price-up {
  animation: pulse-price-up 0.8s ease-in-out;
}

.animate-pulse-price-down {
  animation: pulse-price-down 0.8s ease-in-out;
}

.animate-pulse-change {
  animation: pulse-change 0.8s ease-in-out;
}

.animate-fade-in {
  animation: fade-in 0.3s ease-out;
}
</style>