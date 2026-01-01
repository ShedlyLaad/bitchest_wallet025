<template>
  <div class="min-h-screen bg-gray-900 text-white">
    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl sm:text-4xl font-bold mb-2">Portfolio</h1>
          <p class="text-gray-400 text-sm sm:text-base">View and manage your cryptocurrency assets</p>
        </div>
      </div>

      <!-- Net Worth Section -->
      <div class="bg-gradient-to-br from-gray-800 to-gray-800/95 rounded-2xl p-6 sm:p-8 border border-gray-700/50 shadow-xl">
        <div class="text-gray-400 text-sm font-medium mb-2">Net Worth</div>
        <div class="text-4xl sm:text-5xl font-bold text-white">{{ formatEUR(totalPortfolioValue) }}</div>
        <div class="flex items-center gap-3 mt-3">
          <component 
            :is="totalPL >= 0 ? TrendingUp : TrendingDown" 
            class="h-5 w-5"
            :style="{ color: totalPL >= 0 ? '#01ff19' : '#ff5964' }"
          />
          <div class="text-lg font-semibold" :style="{ color: totalPL >= 0 ? '#01ff19' : '#ff5964' }">
            {{ totalPL >= 0 ? '+' : '' }}{{ formatEUR(Math.abs(totalPL)) }}
          </div>
          <div class="text-gray-400 text-sm">
            ({{ totalPLPercent.toFixed(2) }}%)
          </div>
        </div>
      </div>

      <!-- Performance Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <!-- Total Assets -->
        <div class="bg-gray-800/50 rounded-xl border border-gray-700/50 p-6 shadow-lg">
          <div class="text-gray-400 text-sm font-medium mb-2">Total Assets</div>
          <div class="text-3xl font-bold text-white">{{ portfolioHoldings.length }}</div>
          <div class="text-xs text-gray-500 mt-1">Cryptocurrencies</div>
        </div>

        <!-- Total Invested -->
        <div class="bg-gray-800/50 rounded-xl border border-gray-700/50 p-6 shadow-lg">
          <div class="text-gray-400 text-sm font-medium mb-2">Total Invested</div>
          <div class="text-2xl font-bold text-white">{{ formatEUR(totalInvested) }}</div>
          <div class="text-xs text-gray-500 mt-1">Initial capital</div>
        </div>

        <!-- Return on Investment -->
        <div class="bg-gray-800/50 rounded-xl border border-gray-700/50 p-6 shadow-lg">
          <div class="text-gray-400 text-sm font-medium mb-2">ROI</div>
          <div class="flex items-center gap-2">
            <component 
              :is="totalPLPercent >= 0 ? TrendingUp : TrendingDown" 
              class="h-5 w-5"
              :style="{ color: totalPLPercent >= 0 ? '#01ff19' : '#ff5964' }"
            />
            <div class="text-2xl font-bold" :style="{ color: totalPLPercent >= 0 ? '#01ff19' : '#ff5964' }">
              {{ totalPLPercent >= 0 ? '+' : '' }}{{ totalPLPercent.toFixed(2) }}%
            </div>
          </div>
          <div class="text-xs text-gray-500 mt-1">Return on investment</div>
        </div>

        <!-- Average Gain/Loss -->
        <div class="bg-gray-800/50 rounded-xl border border-gray-700/50 p-6 shadow-lg">
          <div class="text-gray-400 text-sm font-medium mb-2">Avg P/L per Asset</div>
          <div class="text-2xl font-bold" :style="{ color: averagePLPerAsset >= 0 ? '#01ff19' : '#ff5964' }">
            {{ averagePLPerAsset >= 0 ? '+' : '' }}{{ formatEUR(Math.abs(averagePLPerAsset)) }}
          </div>
          <div class="text-xs text-gray-500 mt-1">Per cryptocurrency</div>
        </div>
      </div>

      <!-- Top Performers & Diversification -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Gainers -->
        <div class="bg-gray-800/50 rounded-xl border border-gray-700/50 p-6 shadow-lg">
          <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
            <TrendingUp class="h-5 w-5" style="color: #01ff19;" />
            Top Gainers
          </h2>
          <div v-if="isLoadingPortfolio" class="flex justify-center items-center h-48">
            <div class="h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
          </div>
          <div v-else-if="topGainers.length > 0" class="space-y-3">
              <div
              v-for="holding in topGainers"
              :key="holding.id"
              class="flex items-center justify-between p-3 bg-gray-700/30 rounded-lg hover:bg-gray-700/50 transition-colors"
            >
              <div class="flex items-center gap-3 flex-1">
                <div class="w-8 h-8 bg-gray-700 rounded-full flex items-center justify-center flex-shrink-0 overflow-hidden" :data-symbol="holding.symbol">
                  <img
                    v-if="getCryptoIcon(holding.symbol)"
                    :src="getCryptoIcon(holding.symbol)"
                    :alt="holding.name"
                    class="w-full h-full object-cover"
                    @error="handleImageError($event)"
                  />
                  <span v-else class="text-white font-bold text-xs">{{ holding.symbol }}</span>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="font-semibold text-white text-sm">{{ holding.symbol }}</div>
                  <div class="text-xs text-gray-400 truncate">{{ holding.name }}</div>
                </div>
              </div>
              <div class="text-right">
                <div class="font-bold text-sm" style="color: #01ff19;">+{{ holding.gainLossPercent.toFixed(2) }}%</div>
                <div class="text-xs text-gray-400 font-mono">{{ formatEUR(holding.gainLoss) }}</div>
              </div>
            </div>
          </div>
          <div v-else class="h-48 flex items-center justify-center text-gray-400">
            <p class="text-sm">No gainers yet</p>
          </div>
        </div>

        <!-- Top Losers -->
        <div class="bg-gray-800/50 rounded-xl border border-gray-700/50 p-6 shadow-lg">
          <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
            <TrendingDown class="h-5 w-5" style="color: #ff5964;" />
            Top Losers
          </h2>
          <div v-if="isLoadingPortfolio" class="flex justify-center items-center h-48">
            <div class="h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
          </div>
          <div v-else-if="topLosers.length > 0" class="space-y-3">
              <div
              v-for="holding in topLosers"
              :key="holding.id"
              class="flex items-center justify-between p-3 bg-gray-700/30 rounded-lg hover:bg-gray-700/50 transition-colors"
            >
              <div class="flex items-center gap-3 flex-1">
                <div class="w-8 h-8 bg-gray-700 rounded-full flex items-center justify-center flex-shrink-0 overflow-hidden" :data-symbol="holding.symbol">
                  <img
                    v-if="getCryptoIcon(holding.symbol)"
                    :src="getCryptoIcon(holding.symbol)"
                    :alt="holding.name"
                    class="w-full h-full object-cover"
                    @error="handleImageError($event)"
                  />
                  <span v-else class="text-white font-bold text-xs">{{ holding.symbol }}</span>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="font-semibold text-white text-sm">{{ holding.symbol }}</div>
                  <div class="text-xs text-gray-400 truncate">{{ holding.name }}</div>
                </div>
              </div>
              <div class="text-right">
                <div class="font-bold text-sm" style="color: #ff5964;">{{ holding.gainLossPercent.toFixed(2) }}%</div>
                <div class="text-xs text-gray-400 font-mono">{{ formatEUR(holding.gainLoss) }}</div>
              </div>
            </div>
          </div>
          <div v-else class="h-48 flex items-center justify-center text-gray-400">
            <p class="text-sm">No losers yet</p>
          </div>
        </div>
      </div>

      <!-- Diversification Metrics -->
      <div class="bg-gray-800/50 rounded-xl border border-gray-700/50 p-6 shadow-lg">
        <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
          <Activity class="h-5 w-5 text-blue-400" />
          Diversification Metrics
        </h2>
        <div v-if="isLoadingPortfolio" class="flex justify-center items-center h-32">
          <div class="h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
        </div>
        <div v-else-if="portfolioHoldings.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-gray-700/30 rounded-lg p-4">
            <div class="text-gray-400 text-sm mb-2">Largest Position</div>
            <div class="flex items-center gap-2 mb-1">
              <div class="w-6 h-6 bg-gray-600 rounded-full flex items-center justify-center overflow-hidden" :data-symbol="largestPosition?.symbol">
                <img
                  v-if="largestPosition && getCryptoIcon(largestPosition.symbol)"
                  :src="getCryptoIcon(largestPosition.symbol)"
                  :alt="largestPosition.name"
                  class="w-full h-full object-cover"
                  @error="handleImageError($event)"
                />
                <span v-else-if="largestPosition" class="text-white font-bold text-xs">{{ largestPosition.symbol }}</span>
              </div>
              <div class="font-semibold text-white">{{ largestPosition?.symbol || 'N/A' }}</div>
            </div>
            <div class="text-lg font-bold text-white">{{ largestPosition?.portfolioPercent.toFixed(2) || 0 }}%</div>
          </div>
          <div class="bg-gray-700/30 rounded-lg p-4">
            <div class="text-gray-400 text-sm mb-2">Portfolio Concentration</div>
            <div class="text-lg font-bold text-white">{{ concentrationIndex.toFixed(2) }}</div>
            <div class="text-xs text-gray-500 mt-1">
              <span :style="{ color: concentrationIndex < 0.5 ? '#01ff19' : concentrationIndex < 0.7 ? '#fbbf24' : '#ff5964' }">
                {{ concentrationIndex < 0.5 ? 'Well Diversified' : concentrationIndex < 0.7 ? 'Moderate' : 'Highly Concentrated' }}
              </span>
            </div>
          </div>
          <div class="bg-gray-700/30 rounded-lg p-4">
            <div class="text-gray-400 text-sm mb-2">Best Performer</div>
            <div class="flex items-center gap-2 mb-1">
              <div class="w-6 h-6 bg-gray-600 rounded-full flex items-center justify-center overflow-hidden" :data-symbol="bestPerformer?.symbol">
                <img
                  v-if="bestPerformer && getCryptoIcon(bestPerformer.symbol)"
                  :src="getCryptoIcon(bestPerformer.symbol)"
                  :alt="bestPerformer.name"
                  class="w-full h-full object-cover"
                  @error="handleImageError($event)"
                />
                <span v-else-if="bestPerformer" class="text-white font-bold text-xs">{{ bestPerformer.symbol }}</span>
              </div>
              <div class="font-semibold text-white">{{ bestPerformer?.symbol || 'N/A' }}</div>
            </div>
            <div class="text-lg font-bold" style="color: #01ff19;">+{{ bestPerformer?.gainLossPercent.toFixed(2) || 0 }}%</div>
          </div>
        </div>
        <div v-else class="h-32 flex items-center justify-center text-gray-400">
          <p class="text-sm">No diversification data available</p>
        </div>
      </div>

      <!-- Assets Section -->
      <div class="bg-gray-800/50 rounded-xl border border-gray-700/50 overflow-hidden shadow-lg">
        <div class="p-6 border-b border-gray-700/50 bg-gradient-to-r from-gray-800/80 to-gray-800/60">
          <h2 class="text-xl font-bold flex items-center gap-2">
            Assets
            <div class="w-4 h-4 rounded-full bg-gray-600 flex items-center justify-center cursor-help" title="Your cryptocurrency holdings">
              <span class="text-xs text-gray-400">i</span>
            </div>
          </h2>
        </div>

        <div v-if="isLoadingPortfolio" class="p-12 flex justify-center">
          <div class="h-10 w-10 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
        </div>

        <div v-else-if="portfolioHoldings.length > 0" class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-700/30 border-b border-gray-700/50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Token</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">Portfolio %</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">Price</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">Balance</th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-300 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-700/30">
              <tr
                v-for="holding in portfolioHoldings"
                :key="holding.id"
                class="hover:bg-gray-700/20 transition-colors"
              >
                <td class="px-6 py-5 whitespace-nowrap cursor-pointer" @click="showPurchaseDetails(holding)">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center flex-shrink-0 overflow-hidden" :data-symbol="holding.symbol">
                      <img
                        v-if="getCryptoIcon(holding.symbol)"
                        :src="getCryptoIcon(holding.symbol)"
                        :alt="holding.name"
                        class="w-full h-full object-cover"
                        @error="handleImageError($event)"
                      />
                      <span v-else class="text-white font-bold text-sm">{{ holding.symbol }}</span>
                    </div>
                    <div>
                      <div class="font-semibold text-white text-base">{{ holding.symbol }}</div>
                      <div class="text-sm text-gray-400">{{ holding.name }}</div>
                    </div>
                  </div>
                </td>

                <td class="px-6 py-5 whitespace-nowrap text-right">
                  <div class="font-semibold text-white text-base">{{ holding.portfolioPercent.toFixed(2) }}%</div>
                </td>

                <td class="px-6 py-5 whitespace-nowrap text-right">
                  <div class="font-semibold text-white text-base font-mono">{{ formatEUR(holding.currentPrice) }}</div>
                  <div class="text-xs mt-0.5" :style="{ color: holding.priceChange >= 0 ? '#01ff19' : '#ff5964' }">
                    {{ holding.priceChange >= 0 ? '+' : '' }}{{ holding.priceChange.toFixed(2) }}%
                  </div>
                </td>

                <td class="px-6 py-5 whitespace-nowrap text-right">
                  <div class="font-bold text-white text-base font-mono">{{ formatEUR(holding.value) }}</div>
                  <div class="text-sm text-gray-400 font-mono mt-0.5">{{ holding.quantity.toFixed(8) }} {{ holding.symbol }}</div>
                </td>

                <td class="px-6 py-5 whitespace-nowrap text-center">
                  <button
                    @click.stop="openSellModal(holding)"
                    class="px-4 py-2 text-white font-semibold rounded-lg transition-all duration-200 shadow-md hover:shadow-lg hover:scale-105 active:scale-95"
                    style="background-color: #ff5964;"
                    onmouseover="this.style.backgroundColor='rgba(255, 89, 100, 0.9)'"
                    onmouseout="this.style.backgroundColor='#ff5964'"
                  >
                    Sell
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="p-12 text-center">
          <div class="w-16 h-16 bg-gray-700/30 rounded-full flex items-center justify-center mx-auto mb-4">
            <Wallet class="h-8 w-8 text-gray-500" />
          </div>
          <p class="text-gray-400 mb-2 text-lg">No portfolio holdings</p>
          <p class="text-sm text-gray-500">Start trading to build your portfolio</p>
        </div>
      </div>
    </div>

    <!-- Sell Modal -->
    <div v-if="showSellModal && selectedHolding" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" @click="closeSellModal">
      <div
        class="bg-gray-800 rounded-xl border border-gray-700 w-full max-w-md overflow-hidden shadow-2xl"
        @click.stop
      >
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-700 bg-gradient-to-r from-gray-800 to-gray-800/80">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: rgba(255, 89, 100, 0.2);">
              <TrendingDown class="h-5 w-5" style="color: #ff5964;" />
            </div>
            <div>
              <h2 class="text-xl font-bold">Sell {{ selectedHolding.symbol }}</h2>
              <p class="text-sm text-gray-400">Available: {{ selectedHolding.quantity.toFixed(8) }} {{ selectedHolding.symbol }}</p>
            </div>
          </div>
          <button @click="closeSellModal" class="p-2 hover:bg-gray-700 rounded-lg transition-colors" aria-label="Close">
            <X class="h-5 w-5" />
          </button>
        </div>

        <!-- Modal Content -->
        <div class="p-6 space-y-5">
          <!-- Quantity Input -->
          <div>
            <label class="block text-sm font-semibold text-gray-300 mb-2">
              Quantity ({{ selectedHolding.symbol }})
            </label>
            <input
              v-model="sellQuantity"
              type="number"
              :max="selectedHolding.quantity"
              :step="0.00000001"
              :min="0"
              placeholder="0.00000000"
              class="w-full bg-gray-900/50 border-2 border-gray-700 rounded-xl px-4 py-3.5 text-emerald-400 placeholder-gray-500/60 font-mono text-base font-semibold tracking-wide focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30 transition-all duration-200"
            />
            <div class="mt-2 flex items-center justify-between text-sm">
              <span class="text-gray-400">Current Price:</span>
              <span class="text-white font-semibold font-mono">{{ formatEUR(selectedHolding.currentPrice) }}</span>
            </div>
          </div>

          <!-- Amount Display -->
          <div class="bg-gray-700/30 rounded-xl p-4 border border-gray-700/50">
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm text-gray-400 font-medium">Estimated Value (EUR):</span>
              <span class="text-xl font-bold text-white font-mono">{{ formatEUR(sellAmount) }}</span>
            </div>
          </div>

          <!-- Error Message -->
          <Transition name="slide-fade">
            <div v-if="sellError" class="border-2 backdrop-blur-sm rounded-xl p-4" style="background-color: rgba(255, 89, 100, 0.15); border-color: rgba(255, 89, 100, 0.5);">
              <div class="flex items-start gap-3">
                <div class="w-2 h-2 rounded-full mt-2 flex-shrink-0" style="background-color: #ff5964;"></div>
                <p class="text-sm font-medium" style="color: rgba(255, 89, 100, 0.9);">{{ sellError }}</p>
              </div>
            </div>
          </Transition>

          <!-- Success Message -->
          <Transition name="slide-fade">
            <div v-if="sellSuccess" class="border-2 backdrop-blur-sm rounded-xl p-4" style="background-color: rgba(1, 255, 25, 0.15); border-color: rgba(1, 255, 25, 0.5);">
              <div class="flex items-start gap-3">
                <div class="w-2 h-2 rounded-full mt-2 flex-shrink-0" style="background-color: #01ff19;"></div>
                <p class="text-sm font-medium" style="color: rgba(1, 255, 25, 0.9);">{{ sellSuccess }}</p>
              </div>
            </div>
          </Transition>

          <!-- Action Buttons -->
          <div class="flex gap-3 pt-2">
            <button
              @click="handleSell"
              :disabled="isSelling || !canSell"
              class="flex-1 px-6 py-3 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold rounded-xl transition-all duration-200 hover:scale-[1.02] hover:shadow-xl active:scale-[0.98]"
              style="background-color: #ff5964;"
            >
              {{ isSelling ? 'Selling...' : 'Sell' }}
            </button>
            <button
              @click="closeSellModal"
              class="px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-xl transition-colors"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Purchase Details Modal -->
    <div v-if="isPurchaseDetailsOpen && selectedHolding" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" @click="closePurchaseDetails">
      <div
        class="bg-gray-800 rounded-xl border border-gray-700 w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col shadow-2xl"
        @click.stop
      >
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-700 bg-gradient-to-r from-gray-800 to-gray-800/80">
          <div class="flex items-center gap-3">
            <Wallet class="h-6 w-6 text-blue-400" />
            <h2 class="text-xl font-bold">Purchase Details - {{ selectedHolding.symbol }}</h2>
          </div>
          <button @click="closePurchaseDetails" class="p-2 hover:bg-gray-700 rounded-lg transition-colors" aria-label="Close">
            <X class="h-5 w-5" />
          </button>
        </div>

        <!-- Modal Content -->
        <div class="flex-1 overflow-y-auto p-6">
          <div class="bg-gray-700/30 rounded-lg p-4 mb-4">
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <span class="text-gray-400">Total Quantity:</span>
                <span class="ml-2 font-semibold text-white font-mono">{{ selectedHolding.quantity.toFixed(8) }}</span>
              </div>
              <div>
                <span class="text-gray-400">Average Price:</span>
                <span class="ml-2 font-semibold text-white font-mono">{{ formatEUR(selectedHolding.avgPrice) }}</span>
              </div>
              <div>
                <span class="text-gray-400">Total Invested:</span>
                <span class="ml-2 font-semibold text-white font-mono">{{ formatEUR(selectedHolding.avgPrice * selectedHolding.quantity) }}</span>
              </div>
              <div>
                <span class="text-gray-400">Current Value:</span>
                <span class="ml-2 font-semibold text-white font-mono">{{ formatEUR(selectedHolding.value) }}</span>
              </div>
            </div>
          </div>
          <p class="text-sm text-gray-400 mb-4">Purchase history is calculated based on your average purchase price and total holdings.</p>
          <div class="bg-gray-700/20 rounded-lg p-4 text-center text-gray-400">
            <p>Detailed purchase history will be available in a future update.</p>
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
import { Wallet, X, TrendingUp, TrendingDown, Activity } from 'lucide-vue-next';
import { formatEUR } from '../utils/formatEUR';
import { useAuthStore } from '@/stores/auth';
import { getPortfolio, sellCrypto } from '../services/api';
import { getCryptoIcon } from '../utils/cryptoIcons';
import FooterSection from '../components/sectionsLanding/FooterSection.vue';
import type { PortfolioResponse } from '../types';

const auth = useAuthStore();
const portfolioData = ref<PortfolioResponse | null>(null);
const isLoadingPortfolio = ref(false);
const showSellModal = ref(false);
const isPurchaseDetailsOpen = ref(false);
const selectedHolding = ref<{
  id: number;
  symbol: string;
  name: string;
  quantity: number;
  avgPrice: number;
  currentPrice: number;
  value: number;
  gainLoss: number;
  gainLossPercent: number;
  portfolioPercent: number;
  priceChange: number;
} | null>(null);
const sellQuantity = ref('');
const isSelling = ref(false);
const sellError = ref('');
const sellSuccess = ref('');

// Portfolio holdings for table
const portfolioHoldings = computed(() => {
  if (!portfolioData.value || !portfolioData.value.portfolio.length) {
    return [];
  }

  const totalValue = portfolioData.value.portfolio.reduce((sum, pos) => {
    const qty = pos.quantity || 0;
    const price = pos.current_price || 0;
    return sum + (qty * price);
  }, 0);

  return portfolioData.value.portfolio
    .filter(pos => pos.quantity && pos.quantity > 0)
    .map(pos => {
      const quantity = pos.quantity || 0;
      const currentPrice = pos.current_price || 0;
      const investedValue = pos.invested_value || 0;
      const avgPrice = quantity > 0 ? investedValue / quantity : 0;
      const value = quantity * currentPrice;
      const gainLoss = value - investedValue;
      const gainLossPercent = investedValue > 0 ? (gainLoss / investedValue) * 100 : 0;
      const portfolioPercent = totalValue > 0 ? (value / totalValue) * 100 : 0;
      // Calculate price change (simplified - using gain/loss as proxy)
      const priceChange = avgPrice > 0 ? ((currentPrice - avgPrice) / avgPrice) * 100 : 0;

      return {
        id: pos.id,
        symbol: pos.crypto?.symbol || 'N/A',
        name: pos.crypto?.name || 'Unknown',
        quantity,
        avgPrice,
        currentPrice,
        value,
        gainLoss,
        gainLossPercent,
        portfolioPercent,
        priceChange
      };
    })
    .sort((a, b) => b.value - a.value); // Sort by value descending
});

// Total portfolio value
const totalPortfolioValue = computed(() => {
  return portfolioHoldings.value.reduce((sum, holding) => sum + holding.value, 0);
});

// Total invested value
const totalInvested = computed(() => {
  return portfolioHoldings.value.reduce((sum, holding) => sum + (holding.avgPrice * holding.quantity), 0);
});

// Total portfolio gain/loss
const totalPL = computed(() => {
  return portfolioHoldings.value.reduce((sum, holding) => sum + holding.gainLoss, 0);
});

// Total portfolio gain/loss percentage
const totalPLPercent = computed(() => {
  const invested = totalInvested.value;
  if (invested === 0) return 0;
  return (totalPL.value / invested) * 100;
});

// Average P/L per asset
const averagePLPerAsset = computed(() => {
  if (portfolioHoldings.value.length === 0) return 0;
  return totalPL.value / portfolioHoldings.value.length;
});

// Top gainers (sorted by gain percentage, top 3)
const topGainers = computed(() => {
  return portfolioHoldings.value
    .filter(h => h.gainLossPercent > 0)
    .sort((a, b) => b.gainLossPercent - a.gainLossPercent)
    .slice(0, 3);
});

// Top losers (sorted by loss percentage, top 3)
const topLosers = computed(() => {
  return portfolioHoldings.value
    .filter(h => h.gainLossPercent < 0)
    .sort((a, b) => a.gainLossPercent - b.gainLossPercent)
    .slice(0, 3);
});

// Largest position
const largestPosition = computed(() => {
  if (portfolioHoldings.value.length === 0) return null;
  return portfolioHoldings.value.reduce((max, holding) => 
    holding.portfolioPercent > (max?.portfolioPercent || 0) ? holding : max
  );
});

// Best performer (highest gain percentage)
const bestPerformer = computed(() => {
  if (portfolioHoldings.value.length === 0) return null;
  return portfolioHoldings.value.reduce((best, holding) => 
    holding.gainLossPercent > (best?.gainLossPercent || -Infinity) ? holding : best
  );
});

// Concentration index (Herfindahl-Hirschman Index - sum of squared portfolio percentages)
// Lower = more diversified, Higher = more concentrated
const concentrationIndex = computed(() => {
  if (portfolioHoldings.value.length === 0) return 0;
  const sumOfSquares = portfolioHoldings.value.reduce((sum, holding) => {
    const percent = holding.portfolioPercent / 100;
    return sum + (percent * percent);
  }, 0);
  return sumOfSquares;
});

// Sell amount calculation
const sellAmount = computed(() => {
  if (!selectedHolding.value || !sellQuantity.value) return 0;
  const qty = parseFloat(sellQuantity.value) || 0;
  return qty * selectedHolding.value.currentPrice;
});

// Can sell validation
const canSell = computed(() => {
  if (!selectedHolding.value || !sellQuantity.value) return false;
  const qty = parseFloat(sellQuantity.value) || 0;
  return qty > 0 && qty <= selectedHolding.value.quantity;
});

function handleImageError(event: Event) {
  const target = event.target as HTMLImageElement;
  if (target) {
    target.style.display = 'none';
    const parent = target.parentElement;
    if (parent) {
      const span = document.createElement('span');
      span.className = 'text-white font-bold text-sm';
      span.textContent = parent.getAttribute('data-symbol') || '';
      parent.appendChild(span);
    }
  }
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

function openSellModal(holding: typeof portfolioHoldings.value[0]) {
  selectedHolding.value = holding;
  sellQuantity.value = '';
  sellError.value = '';
  sellSuccess.value = '';
  showSellModal.value = true;
}

function closeSellModal() {
  showSellModal.value = false;
  selectedHolding.value = null;
  sellQuantity.value = '';
  sellError.value = '';
  sellSuccess.value = '';
}

function showPurchaseDetails(holding: typeof portfolioHoldings.value[0]) {
  selectedHolding.value = holding;
  isPurchaseDetailsOpen.value = true;
}

function closePurchaseDetails() {
  isPurchaseDetailsOpen.value = false;
  selectedHolding.value = null;
}

async function handleSell() {
  if (isSelling.value || !canSell.value || !selectedHolding.value) return;
  
  sellError.value = '';
  sellSuccess.value = '';
  isSelling.value = true;
  
  try {
    const quantity = parseFloat(sellQuantity.value);
    
    if (quantity <= 0) {
      sellError.value = 'Please enter a valid quantity greater than 0';
      return;
    }
    
    if (quantity > selectedHolding.value.quantity) {
      sellError.value = `Insufficient quantity. Available: ${selectedHolding.value.quantity.toFixed(8)} ${selectedHolding.value.symbol}`;
      return;
    }
    
    // Execute sell
    const sellResponse = await sellCrypto({
      symbol: selectedHolding.value.symbol,
      quantity: quantity
    });
    
    // Update user balance from response
    if (auth.user) {
      auth.user.euro_balance = sellResponse.balance;
      if (auth.persist) {
        auth.persist();
      }
    }
    
    sellSuccess.value = `Successfully sold ${quantity.toFixed(8)} ${selectedHolding.value.symbol}`;
    
    // Reload portfolio to update holdings
    await loadPortfolio();
    
    // Close modal after 2 seconds
    setTimeout(() => {
      closeSellModal();
    }, 2000);
    
  } catch (error: any) {
    console.error('Sell error:', error);
    const errorMessage = error?.response?.data?.message || error?.message || 'An error occurred while processing your sale';
    sellError.value = errorMessage;
  } finally {
    isSelling.value = false;
  }
}

onMounted(async () => {
  if (!auth.user && auth.token) {
    await auth.fetchCurrentUser();
  }
  await loadPortfolio();
});
</script>

<style scoped>
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.2s ease-in;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(-10px);
  opacity: 0;
}
</style>
