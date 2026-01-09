<template>
  <div class="min-h-screen bg-gray-900 text-white relative overflow-hidden">
    <!-- Enhanced Animated Background -->
    <div class="absolute inset-0 pointer-events-none z-0">
      <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-gray-900 via-gray-900 to-gray-950"></div>
      <div 
        class="absolute top-1/4 -left-40 w-96 h-96 rounded-full blur-3xl opacity-10 animate-pulse"
        :style="{ backgroundColor: 'var(--blue-dark)' }"
      ></div>
      <div 
        class="absolute bottom-1/4 -right-40 w-96 h-96 rounded-full blur-3xl opacity-10 animate-pulse delay-1000"
        :style="{ backgroundColor: 'var(--blue)' }"
      ></div>
      <div class="absolute inset-0 opacity-[0.02]" style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 50px 50px;"></div>
    </div>

    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6 relative z-10">
      <!-- Enhanced Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
          <div class="p-3 bg-gradient-to-br from-blue-600/20 to-blue-800/10 rounded-xl border border-blue-500/30 backdrop-blur-sm">
            <Wallet class="h-6 w-6 text-blue-400" />
          </div>
          <div>
            <h1 class="text-3xl sm:text-4xl font-bold mb-2 bg-gradient-to-r from-white via-gray-200 to-gray-300 bg-clip-text text-transparent">Portfolio</h1>
            <p class="text-gray-400 text-sm sm:text-base">View and manage your cryptocurrency assets</p>
          </div>
        </div>
      </div>

      <!-- Enhanced Net Worth Section -->
      <div class="group relative bg-gradient-to-br from-gray-800/60 to-gray-800/40 backdrop-blur-xl rounded-2xl p-6 sm:p-8 border border-gray-700/50 shadow-xl hover:shadow-2xl hover:shadow-blue-500/20 transition-all duration-300 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/0 to-purple-500/0 group-hover:from-blue-500/5 group-hover:to-purple-500/5 transition-all duration-300"></div>
        <div class="relative">
          <div class="flex items-center gap-2 mb-3">
            <div class="p-2 bg-blue-500/20 rounded-lg">
              <TrendingUp class="h-5 w-5 text-blue-400" />
            </div>
            <div class="text-gray-400 text-sm font-medium">Net Worth</div>
          </div>
          <div class="text-4xl sm:text-5xl font-bold text-white mb-4 transition-transform duration-300 group-hover:scale-105">{{ formatEUR(totalPortfolioValue) }}</div>
          <div class="flex items-center gap-3 mt-3">
            <div 
              class="p-2 rounded-lg transition-all duration-300 group-hover:scale-110"
              :style="totalPL >= 0 
                ? { backgroundColor: 'rgba(1, 255, 25, 0.2)', border: '1px solid rgba(1, 255, 25, 0.3)' } 
                : { backgroundColor: 'rgba(255, 89, 100, 0.2)', border: '1px solid rgba(255, 89, 100, 0.3)' }"
            >
              <component 
                :is="totalPL >= 0 ? TrendingUp : TrendingDown" 
                class="h-5 w-5"
                :style="{ color: totalPL >= 0 ? '#01ff19' : '#ff5964' }"
              />
            </div>
            <div class="text-lg font-semibold" :style="{ color: totalPL >= 0 ? '#01ff19' : '#ff5964' }">
              {{ totalPL >= 0 ? '+' : '' }}{{ formatEUR(Math.abs(totalPL)) }}
            </div>
            <div class="px-3 py-1 rounded-lg text-sm font-medium" :style="totalPLPercent >= 0 
              ? { backgroundColor: 'rgba(1, 255, 25, 0.2)', color: '#01ff19', border: '1px solid rgba(1, 255, 25, 0.3)' } 
              : { backgroundColor: 'rgba(255, 89, 100, 0.2)', color: '#ff5964', border: '1px solid rgba(255, 89, 100, 0.3)' }">
              ({{ totalPLPercent >= 0 ? '+' : '' }}{{ totalPLPercent.toFixed(2) }}%)
            </div>
          </div>
        </div>
      </div>

      <!-- Enhanced Performance Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <!-- Total Assets -->
        <div class="group relative bg-gradient-to-br from-gray-800/60 to-gray-800/40 backdrop-blur-sm rounded-xl border border-gray-700/50 p-6 shadow-lg hover:shadow-xl hover:shadow-blue-500/20 transition-all duration-300 hover:scale-105 hover:border-blue-500/50 overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-r from-blue-500/0 to-cyan-500/0 group-hover:from-blue-500/10 group-hover:to-cyan-500/10 transition-all duration-300"></div>
          <div class="relative flex items-center gap-3 mb-3">
            <div class="p-2 bg-blue-500/20 rounded-lg group-hover:bg-blue-500/30 transition-colors">
              <Activity class="h-5 w-5 text-blue-400" />
            </div>
            <div class="text-gray-400 text-sm font-medium">Total Assets</div>
          </div>
          <div class="text-3xl font-bold text-white mb-1 transition-transform duration-300 group-hover:scale-110">{{ portfolioHoldings.length }}</div>
          <div class="text-xs text-gray-500">Cryptocurrencies</div>
        </div>

        <!-- Total Invested -->
        <div class="group relative bg-gradient-to-br from-gray-800/60 to-gray-800/40 backdrop-blur-sm rounded-xl border border-gray-700/50 p-6 shadow-lg hover:shadow-xl hover:shadow-purple-500/20 transition-all duration-300 hover:scale-105 hover:border-purple-500/50 overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-r from-purple-500/0 to-pink-500/0 group-hover:from-purple-500/10 group-hover:to-pink-500/10 transition-all duration-300"></div>
          <div class="relative flex items-center gap-3 mb-3">
            <div class="p-2 bg-purple-500/20 rounded-lg group-hover:bg-purple-500/30 transition-colors">
              <TrendingUp class="h-5 w-5 text-purple-400" />
            </div>
            <div class="text-gray-400 text-sm font-medium">Total Invested</div>
          </div>
          <div class="text-2xl font-bold text-white mb-1 transition-transform duration-300 group-hover:scale-110">{{ formatEUR(totalInvested) }}</div>
          <div class="text-xs text-gray-500">Initial capital</div>
        </div>

        <!-- Return on Investment -->
        <div class="group relative bg-gradient-to-br from-gray-800/60 to-gray-800/40 backdrop-blur-sm rounded-xl border border-gray-700/50 p-6 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 overflow-hidden" :style="totalPLPercent >= 0 
          ? { boxShadow: '0 10px 30px rgba(1, 255, 25, 0.2)' } 
          : { boxShadow: '0 10px 30px rgba(255, 89, 100, 0.2)' }" :class="totalPLPercent >= 0 ? 'hover:border-green-500/50' : 'hover:border-red-500/50'">
          <div class="absolute inset-0 transition-all duration-300" :style="totalPLPercent >= 0 
            ? { background: 'linear-gradient(to right, rgba(1, 255, 25, 0), rgba(1, 255, 25, 0))' } 
            : { background: 'linear-gradient(to right, rgba(255, 89, 100, 0), rgba(255, 89, 100, 0))' }" :class="totalPLPercent >= 0 ? 'group-hover:from-green-500/10 group-hover:to-emerald-500/10' : 'group-hover:from-red-500/10 group-hover:to-pink-500/10'"></div>
          <div class="relative flex items-center gap-3 mb-3">
            <div class="p-2 rounded-lg group-hover:scale-110 transition-all duration-300" :style="totalPLPercent >= 0 
              ? { backgroundColor: 'rgba(1, 255, 25, 0.2)', border: '1px solid rgba(1, 255, 25, 0.3)' } 
              : { backgroundColor: 'rgba(255, 89, 100, 0.2)', border: '1px solid rgba(255, 89, 100, 0.3)' }">
              <component 
                :is="totalPLPercent >= 0 ? TrendingUp : TrendingDown" 
                class="h-5 w-5"
                :style="{ color: totalPLPercent >= 0 ? '#01ff19' : '#ff5964' }"
              />
            </div>
            <div class="text-gray-400 text-sm font-medium">ROI</div>
          </div>
          <div class="flex items-center gap-2 mb-1">
            <div class="text-2xl font-bold transition-transform duration-300 group-hover:scale-110" :style="{ color: totalPLPercent >= 0 ? '#01ff19' : '#ff5964' }">
              {{ totalPLPercent >= 0 ? '+' : '' }}{{ totalPLPercent.toFixed(2) }}%
            </div>
          </div>
          <div class="text-xs text-gray-500">Return on investment</div>
        </div>

        <!-- Average Gain/Loss -->
        <div class="group relative bg-gradient-to-br from-gray-800/60 to-gray-800/40 backdrop-blur-sm rounded-xl border border-gray-700/50 p-6 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 overflow-hidden" :style="averagePLPerAsset >= 0 
          ? { boxShadow: '0 10px 30px rgba(1, 255, 25, 0.2)' } 
          : { boxShadow: '0 10px 30px rgba(255, 89, 100, 0.2)' }" :class="averagePLPerAsset >= 0 ? 'hover:border-green-500/50' : 'hover:border-red-500/50'">
          <div class="absolute inset-0 transition-all duration-300" :style="averagePLPerAsset >= 0 
            ? { background: 'linear-gradient(to right, rgba(1, 255, 25, 0), rgba(1, 255, 25, 0))' } 
            : { background: 'linear-gradient(to right, rgba(255, 89, 100, 0), rgba(255, 89, 100, 0))' }" :class="averagePLPerAsset >= 0 ? 'group-hover:from-green-500/10 group-hover:to-emerald-500/10' : 'group-hover:from-red-500/10 group-hover:to-pink-500/10'"></div>
          <div class="relative flex items-center gap-3 mb-3">
            <div class="p-2 rounded-lg group-hover:scale-110 transition-all duration-300" :style="averagePLPerAsset >= 0 
              ? { backgroundColor: 'rgba(1, 255, 25, 0.2)', border: '1px solid rgba(1, 255, 25, 0.3)' } 
              : { backgroundColor: 'rgba(255, 89, 100, 0.2)', border: '1px solid rgba(255, 89, 100, 0.3)' }">
              <component 
                :is="averagePLPerAsset >= 0 ? TrendingUp : TrendingDown" 
                class="h-5 w-5"
                :style="{ color: averagePLPerAsset >= 0 ? '#01ff19' : '#ff5964' }"
              />
            </div>
            <div class="text-gray-400 text-sm font-medium">Avg P/L per Asset</div>
          </div>
          <div class="text-2xl font-bold mb-1 transition-transform duration-300 group-hover:scale-110" :style="{ color: averagePLPerAsset >= 0 ? '#01ff19' : '#ff5964' }">
            {{ averagePLPerAsset >= 0 ? '+' : '' }}{{ formatEUR(Math.abs(averagePLPerAsset)) }}
          </div>
          <div class="text-xs text-gray-500">Per cryptocurrency</div>
        </div>
      </div>

      <!-- Top Performers & Diversification -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Enhanced Top Gainers -->
        <div class="bg-gradient-to-br from-gray-800/60 to-gray-800/40 backdrop-blur-sm rounded-xl border border-gray-700/50 p-6 shadow-lg hover:shadow-xl hover:shadow-green-500/20 transition-all duration-300">
          <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
            <div class="p-2 bg-green-500/20 rounded-lg">
              <TrendingUp class="h-5 w-5 text-green-400" />
            </div>
            <span>Top Gainers</span>
          </h2>
          <div v-if="isLoadingPortfolio" class="flex justify-center items-center h-48">
            <div class="h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
          </div>
          <div v-else-if="topGainers.length > 0" class="space-y-3">
              <div
              v-for="holding in topGainers"
              :key="holding.id"
              class="group flex items-center justify-between p-4 bg-gradient-to-br from-gray-700/40 to-gray-800/40 backdrop-blur-sm rounded-xl border border-gray-700/50 hover:border-green-500/50 hover:shadow-lg hover:shadow-green-500/10 transition-all duration-300 hover:scale-[1.02]"
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
                <div class="font-bold text-sm" style="color: #01ff19;">
                  {{ holding.gainLossPercent >= 0 ? '+' : '' }}{{ holding.gainLossPercent.toFixed(2) }}%
                </div>
                <div class="text-xs text-gray-400 font-mono">
                  {{ holding.gainLoss >= 0 ? '+' : '' }}{{ formatEUR(Math.abs(holding.gainLoss)) }}
                </div>
              </div>
            </div>
          </div>
          <div v-else class="h-48 flex items-center justify-center text-gray-400">
            <p class="text-sm">No gainers yet</p>
          </div>
        </div>

        <!-- Enhanced Top Losers -->
        <div class="bg-gradient-to-br from-gray-800/60 to-gray-800/40 backdrop-blur-sm rounded-xl border border-gray-700/50 p-6 shadow-lg hover:shadow-xl hover:shadow-red-500/20 transition-all duration-300">
          <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
            <div class="p-2 bg-red-500/20 rounded-lg">
              <TrendingDown class="h-5 w-5 text-red-400" />
            </div>
            <span>Top Losers</span>
          </h2>
          <div v-if="isLoadingPortfolio" class="flex justify-center items-center h-48">
            <div class="h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
          </div>
          <div v-else-if="topLosers.length > 0" class="space-y-3">
              <div
              v-for="holding in topLosers"
              :key="holding.id"
              class="group flex items-center justify-between p-4 bg-gradient-to-br from-gray-700/40 to-gray-800/40 backdrop-blur-sm rounded-xl border border-gray-700/50 hover:border-red-500/50 hover:shadow-lg hover:shadow-red-500/10 transition-all duration-300 hover:scale-[1.02]"
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
                <div class="font-bold text-sm" style="color: #ff5964;">
                  {{ holding.gainLossPercent < 0 ? '-' : '' }}{{ Math.abs(holding.gainLossPercent).toFixed(2) }}%
                </div>
                <div class="text-xs text-gray-400 font-mono">
                  {{ holding.gainLoss < 0 ? '-' : '' }}{{ formatEUR(Math.abs(holding.gainLoss)) }}
                </div>
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
          <div class="group bg-gradient-to-br from-gray-700/40 to-gray-800/40 backdrop-blur-sm rounded-xl border border-gray-700/50 p-4 shadow-lg hover:shadow-xl hover:shadow-blue-500/20 transition-all duration-300 hover:scale-105 hover:border-blue-500/50">
            <div class="flex items-center gap-2 mb-3">
              <div class="p-1.5 bg-blue-500/20 rounded-lg group-hover:bg-blue-500/30 transition-colors">
                <TrendingUp class="h-4 w-4 text-blue-400" />
              </div>
              <div class="text-gray-400 text-sm font-medium">Largest Position</div>
            </div>
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
          <div class="group bg-gradient-to-br from-gray-700/40 to-gray-800/40 backdrop-blur-sm rounded-xl border border-gray-700/50 p-4 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 hover:border-purple-500/50">
            <div class="flex items-center gap-2 mb-3">
              <div class="p-1.5 bg-purple-500/20 rounded-lg group-hover:bg-purple-500/30 transition-colors">
                <Activity class="h-4 w-4 text-purple-400" />
              </div>
              <div class="text-gray-400 text-sm font-medium">Portfolio Concentration</div>
            </div>
            <div class="text-lg font-bold text-white mb-2 transition-transform duration-300 group-hover:scale-110">{{ concentrationIndex.toFixed(2) }}</div>
            <div class="text-xs font-medium mt-1 px-2 py-1 rounded-lg inline-block" :style="{ 
              backgroundColor: concentrationIndex < 0.5 ? 'rgba(1, 255, 25, 0.2)' : concentrationIndex < 0.7 ? 'rgba(251, 191, 36, 0.2)' : 'rgba(255, 89, 100, 0.2)',
              color: concentrationIndex < 0.5 ? '#01ff19' : concentrationIndex < 0.7 ? '#fbbf24' : '#ff5964',
              border: `1px solid ${concentrationIndex < 0.5 ? 'rgba(1, 255, 25, 0.3)' : concentrationIndex < 0.7 ? 'rgba(251, 191, 36, 0.3)' : 'rgba(255, 89, 100, 0.3)'}`
            }">
              {{ concentrationIndex < 0.5 ? 'Well Diversified' : concentrationIndex < 0.7 ? 'Moderate' : 'Highly Concentrated' }}
            </div>
          </div>
          <div class="group bg-gradient-to-br from-gray-700/40 to-gray-800/40 backdrop-blur-sm rounded-xl border border-gray-700/50 p-4 shadow-lg hover:shadow-xl hover:shadow-green-500/20 transition-all duration-300 hover:scale-105 hover:border-green-500/50">
            <div class="flex items-center gap-2 mb-3">
              <div class="p-1.5 bg-green-500/20 rounded-lg group-hover:bg-green-500/30 transition-colors">
                <TrendingUp class="h-4 w-4 text-green-400" />
              </div>
              <div class="text-gray-400 text-sm font-medium">Best Performer</div>
            </div>
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
            <div class="text-lg font-bold" :style="{ color: (bestPerformer?.gainLossPercent ?? 0) >= 0 ? '#01ff19' : '#ff5964' }">
              {{ (bestPerformer?.gainLossPercent ?? 0) >= 0 ? '+' : '' }}{{ Math.abs(bestPerformer?.gainLossPercent ?? 0).toFixed(2) }}%
            </div>
          </div>
        </div>
        <div v-else class="h-32 flex items-center justify-center text-gray-400">
          <p class="text-sm">No diversification data available</p>
        </div>
      </div>

      <!-- Enhanced Assets Section -->
      <div class="bg-gradient-to-br from-gray-800/60 to-gray-800/40 backdrop-blur-xl rounded-xl border border-gray-700/50 overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300">
        <div class="p-6 border-b border-gray-700/50 bg-gradient-to-r from-gray-800/80 to-gray-800/60">
          <h2 class="text-xl font-bold flex items-center gap-3">
            <div class="p-2 bg-blue-500/20 rounded-lg">
              <Wallet class="h-5 w-5 text-blue-400" />
            </div>
            <span>Assets</span>
            <div class="w-4 h-4 rounded-full bg-gray-600/50 flex items-center justify-center cursor-help border border-gray-700/50" title="Your cryptocurrency holdings">
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
                class="group hover:bg-gradient-to-r hover:from-gray-700/30 hover:to-gray-800/30 transition-all duration-300"
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
                  <div class="text-xs mt-0.5 font-medium" :style="{ color: holding.priceChange >= 0 ? '#01ff19' : '#ff5964' }">
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
          <!-- Quick Info Cards -->
          <div class="grid grid-cols-2 gap-3">
            <div class="bg-gradient-to-br from-gray-700/40 to-gray-800/40 rounded-lg p-3 border border-gray-600/30">
              <div class="text-xs text-gray-400 mb-1">Available Balance</div>
              <div class="text-sm font-bold text-white font-mono">{{ selectedHolding.quantity.toFixed(8) }}</div>
              <div class="text-xs text-gray-500 mt-0.5">{{ selectedHolding.symbol }}</div>
            </div>
            <div class="bg-gradient-to-br from-gray-700/40 to-gray-800/40 rounded-lg p-3 border border-gray-600/30">
              <div class="text-xs text-gray-400 mb-1">Current Price</div>
              <div class="text-sm font-bold text-white font-mono">{{ formatEUR(selectedHolding.currentPrice) }}</div>
              <div class="text-xs mt-0.5" :style="{ color: selectedHolding.priceChange >= 0 ? '#01ff19' : '#ff5964' }">
                {{ selectedHolding.priceChange >= 0 ? '+' : '' }}{{ selectedHolding.priceChange.toFixed(2) }}%
              </div>
            </div>
          </div>

          <!-- Quantity Input with Sell All Button -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <label class="block text-sm font-semibold text-gray-300">
              Quantity ({{ selectedHolding.symbol }})
            </label>
              <button
                @click="sellAllQuantity"
                class="group relative px-3 py-1.5 text-xs font-semibold rounded-lg transition-all duration-200 hover:scale-105 active:scale-95 overflow-hidden"
                style="background: linear-gradient(135deg, rgba(255, 89, 100, 0.2), rgba(255, 89, 100, 0.15)); border: 1px solid rgba(255, 89, 100, 0.3); color: #ff5964;"
              >
                <span class="relative z-10 flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5 transition-transform group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                  </svg>
                  Sell All
                </span>
                <div class="absolute inset-0 bg-gradient-to-r from-red-500/0 via-red-500/0 to-red-500/0 group-hover:from-red-500/10 group-hover:via-red-500/5 group-hover:to-red-500/0 transition-all duration-300"></div>
              </button>
            </div>
            <div class="relative">
            <input
              v-model="sellQuantity"
              type="number"
              :max="selectedHolding.quantity"
              :step="0.00000001"
              :min="0"
              placeholder="0.00000000"
                class="w-full bg-gray-900/50 border-2 border-gray-700 rounded-xl px-4 pr-24 py-3.5 text-emerald-400 placeholder-gray-500/60 font-mono text-base font-semibold tracking-wide focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30 transition-all duration-200"
            />
              <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-2">
                <span class="text-xs text-gray-500 font-medium">{{ selectedHolding.symbol }}</span>
                <div class="h-4 w-px bg-gray-600"></div>
                <button
                  @click="setHalfQuantity"
                  class="px-2 py-1 text-xs font-medium text-gray-400 hover:text-white hover:bg-gray-700/50 rounded transition-colors"
                  title="Set 50%"
                >
                  50%
                </button>
              </div>
            </div>
            <!-- Quantity Progress Bar -->
            <div class="mt-2">
              <div class="flex items-center justify-between text-xs text-gray-400 mb-1">
                <span>Quantity entered</span>
                <span>{{ quantityPercentage.toFixed(1) }}%</span>
              </div>
              <div class="w-full h-1.5 bg-gray-700/50 rounded-full overflow-hidden">
                <div
                  class="h-full rounded-full transition-all duration-300"
                  :style="{
                    width: `${Math.min(100, quantityPercentage)}%`,
                    background: 'linear-gradient(90deg, rgba(255, 89, 100, 0.6), rgba(255, 89, 100, 0.8))'
                  }"
                ></div>
              </div>
            </div>
          </div>

          <!-- Enhanced Amount Display -->
          <div class="bg-gradient-to-br from-gray-700/40 via-gray-800/40 to-gray-700/40 rounded-xl p-5 border border-gray-600/30 shadow-lg">
            <div class="flex items-center justify-between mb-3">
              <span class="text-sm text-gray-400 font-medium">Estimated Value (EUR)</span>
              <div class="w-8 h-8 rounded-full bg-gray-700/50 flex items-center justify-center border border-gray-600/30">
                <TrendingDown class="h-4 w-4 text-gray-400" />
              </div>
            </div>
            <div class="text-3xl font-bold text-white font-mono mb-2">{{ formatEUR(sellAmount) }}</div>
            <div class="flex items-center justify-between text-xs text-gray-500 pt-2 border-t border-gray-700/50">
              <span>Quantity: {{ (parseFloat(sellQuantity) || 0).toFixed(8) }} {{ selectedHolding.symbol }}</span>
              <span>Price: {{ formatEUR(selectedHolding.currentPrice) }}</span>
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
          <div class="flex flex-col gap-3 pt-2">
            <!-- Primary Sell Button -->
            <button
              @click="handleSell"
              :disabled="isSelling || !canSell"
              class="group relative w-full px-6 py-4 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold rounded-xl transition-all duration-200 hover:scale-[1.02] hover:shadow-xl active:scale-[0.98] overflow-hidden"
              style="background: linear-gradient(135deg, #ff5964, #ff4757); box-shadow: 0 4px 14px rgba(255, 89, 100, 0.3);"
            >
              <span class="relative z-10 flex items-center justify-center gap-2">
                <svg v-if="!isSelling" class="w-5 h-5 transition-transform group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ isSelling ? 'Processing Sale...' : `Sell ${(parseFloat(sellQuantity) || 0).toFixed(8)} ${selectedHolding.symbol}` }}
              </span>
              <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/0 to-transparent group-hover:via-white/10 transition-all duration-700 transform -translate-x-full group-hover:translate-x-full"></div>
            </button>
            
            <!-- Secondary Cancel Button -->
            <button
              @click="closeSellModal"
              class="px-6 py-3 bg-gray-700/50 hover:bg-gray-600/50 border border-gray-600/50 text-white font-semibold rounded-xl transition-all duration-200 hover:scale-[1.01] active:scale-[0.99]"
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
                <span class="text-gray-400">Average Purchase Price:</span>
                <span class="ml-2 font-semibold text-white font-mono">{{ formatEUR(selectedHolding.avgPrice) }}</span>
              </div>
              <div>
                <span class="text-gray-400">Current Price:</span>
                <span class="ml-2 font-semibold text-white font-mono">{{ formatEUR(selectedHolding.currentPrice) }}</span>
              </div>
              <div>
                <span class="text-gray-400">Total Invested:</span>
                <span class="ml-2 font-semibold text-white font-mono">{{ formatEUR(selectedHolding.totalInvestedValue || (selectedHolding.avgPrice * selectedHolding.quantity)) }}</span>
              </div>
              <div>
                <span class="text-gray-400">Current Value:</span>
                <span class="ml-2 font-semibold text-white font-mono">{{ formatEUR(selectedHolding.value) }}</span>
              </div>
              <div>
                <span class="text-gray-400">Gain/Loss:</span>
                <span :class="['ml-2 font-semibold font-mono', selectedHolding.gainLoss >= 0 ? 'text-green-400' : 'text-red-400']">
                  {{ selectedHolding.gainLoss >= 0 ? '+' : '' }}{{ formatEUR(Math.abs(selectedHolding.gainLoss)) }}
                  ({{ selectedHolding.gainLossPercent >= 0 ? '+' : '' }}{{ Math.abs(selectedHolding.gainLossPercent).toFixed(2) }}%)
                </span>
              </div>
            </div>
          </div>
          <div v-if="isLoadingPurchaseDetails" class="flex justify-center items-center py-8">
            <div class="h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
          </div>
          <div v-else-if="purchaseDetails.length > 0" class="space-y-3">
            <div class="text-sm font-semibold text-gray-300 mb-3">Purchase History</div>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead>
                  <tr class="border-b border-gray-700">
                    <th class="text-left py-2 px-3 text-gray-400 font-semibold">Date</th>
                    <th class="text-right py-2 px-3 text-gray-400 font-semibold">Quantity</th>
                    <th class="text-right py-2 px-3 text-gray-400 font-semibold">Price</th>
                    <th class="text-right py-2 px-3 text-gray-400 font-semibold">Total Cost</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="purchase in purchaseDetails" :key="purchase.id" class="border-b border-gray-700/50 hover:bg-gray-700/20">
                    <td class="py-2 px-3 text-gray-300">{{ new Date(purchase.datetime).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' }) }}</td>
                    <td class="py-2 px-3 text-right text-white font-mono">{{ purchase.quantity.toFixed(8) }}</td>
                    <td class="py-2 px-3 text-right text-white font-mono">{{ formatEUR(purchase.price) }}</td>
                    <td class="py-2 px-3 text-right text-white font-mono font-semibold">{{ formatEUR(purchase.total_cost) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="bg-gray-700/30 rounded-lg p-4 mt-4">
              <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="text-gray-400">Total Purchases:</span>
                  <span class="ml-2 font-semibold text-white">{{ purchaseDetails.length }}</span>
                </div>
                <div>
                  <span class="text-gray-400">Total Quantity Bought:</span>
                  <span class="ml-2 font-semibold text-white font-mono">{{ purchaseDetails.reduce((sum, p) => sum + p.quantity, 0).toFixed(8) }}</span>
                </div>
                <div class="col-span-2">
                  <span class="text-gray-400">Total Cost:</span>
                  <span class="ml-2 font-semibold text-white font-mono">{{ formatEUR(purchaseDetails.reduce((sum, p) => sum + p.total_cost, 0)) }}</span>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="bg-gray-700/20 rounded-lg p-4 text-center text-gray-400">
            <p>No purchase history available.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer - Minimal -->
    <UserFooter />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Wallet, X, TrendingUp, TrendingDown, Activity } from 'lucide-vue-next';
import { formatEUR } from '../utils/formatEUR';
import { useAuthStore } from '@/stores/auth';
import { getPortfolio, sellCrypto, getPurchaseDetails } from '../services/api';
import { getCryptoIcon } from '../utils/cryptoIcons';
import UserFooter from '../components/UserFooter.vue';
import type { PortfolioResponse } from '../types';

const auth = useAuthStore();
const portfolioData = ref<PortfolioResponse | null>(null);
const isLoadingPortfolio = ref(false);
const showSellModal = ref(false);
const isPurchaseDetailsOpen = ref(false);
const selectedHolding = ref<{
  id: number;
  cryptoCurrencyId?: number;
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
  totalInvestedValue?: number;
  totalCost?: number;
  buyTransactionsCount?: number;
} | null>(null);

const purchaseDetails = ref<Array<{ id: number; date: string; datetime: string; quantity: number; price: number; total_cost: number }>>([]);
const isLoadingPurchaseDetails = ref(false);
const sellQuantity = ref('');
const isSelling = ref(false);
const sellError = ref('');
const sellSuccess = ref('');
let portfolioRefreshTimer: ReturnType<typeof setInterval> | null = null;

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
      // Prix actuel depuis Coinbase API via le backend
      const currentPrice = pos.current_price || 0;
      const averagePurchasePrice = pos.average_purchase_price || 0;
      const totalInvestedValue = pos.total_invested_value || 0;
      // Utiliser les valeurs calculées par le backend (basées sur Coinbase API)
      // Le backend calcule current_value, gain_loss et gain_loss_percent avec les prix réels de Coinbase
      const value = pos.current_value !== undefined && pos.current_value !== null 
        ? Number(pos.current_value) 
        : (quantity * currentPrice);
      const gainLoss = pos.gain_loss !== undefined && pos.gain_loss !== null 
        ? Number(pos.gain_loss) 
        : (value - totalInvestedValue);
      // Utiliser gain_loss_percent du backend (calculé avec les prix Coinbase réels)
      const gainLossPercent = pos.gain_loss_percent !== undefined && pos.gain_loss_percent !== null 
        ? Number(pos.gain_loss_percent) 
        : (totalInvestedValue > 0 ? ((gainLoss / totalInvestedValue) * 100) : 0);
      const portfolioPercent = totalValue > 0 ? (value / totalValue) * 100 : 0;
      const priceChange = averagePurchasePrice > 0 ? ((currentPrice - averagePurchasePrice) / averagePurchasePrice) * 100 : 0;
      const cryptoCurrencyId = pos.crypto_currency_id;

      return {
        id: pos.id,
        cryptoCurrencyId,
        symbol: pos.crypto?.symbol || 'N/A',
        name: pos.crypto?.name || 'Unknown',
        quantity,
        avgPrice: averagePurchasePrice,
        currentPrice,
        value,
        gainLoss,
        gainLossPercent,
        portfolioPercent,
        priceChange,
        totalInvestedValue,
        totalCost: pos.total_cost || 0,
        buyTransactionsCount: pos.buy_transactions_count || 0
      };
    })
    .sort((a, b) => b.value - a.value); // Sort by value descending
});

// Total portfolio value
const totalPortfolioValue = computed(() => {
  return portfolioHoldings.value.reduce((sum, holding) => sum + holding.value, 0);
});

// Total invested value (selon cahier des charges: coût total investi)
const totalInvested = computed(() => {
  return portfolioHoldings.value.reduce((sum, holding) => sum + (holding.totalInvestedValue || 0), 0);
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

// Quantity percentage for progress bar
const quantityPercentage = computed(() => {
  if (!selectedHolding.value || !sellQuantity.value) return 0;
  const qty = parseFloat(sellQuantity.value) || 0;
  if (selectedHolding.value.quantity <= 0) return 0;
  return (qty / selectedHolding.value.quantity) * 100;
});

// Can sell validation
const canSell = computed(() => {
  if (!selectedHolding.value || !sellQuantity.value) return false;
  const qty = parseFloat(sellQuantity.value) || 0;
  return qty > 0 && qty <= selectedHolding.value.quantity;
});

// Set quantity to all available
function sellAllQuantity() {
  if (!selectedHolding.value) return;
  sellQuantity.value = selectedHolding.value.quantity.toFixed(8);
}

// Set quantity to half (50%)
function setHalfQuantity() {
  if (!selectedHolding.value) return;
  const halfQuantity = selectedHolding.value.quantity / 2;
  sellQuantity.value = halfQuantity.toFixed(8);
}

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

async function showPurchaseDetails(holding: typeof portfolioHoldings.value[0]) {
  selectedHolding.value = holding;
  isPurchaseDetailsOpen.value = true;
  
  // Charger les détails des achats
  if (holding.cryptoCurrencyId) {
    isLoadingPurchaseDetails.value = true;
    try {
      const response = await getPurchaseDetails(holding.cryptoCurrencyId);
      purchaseDetails.value = response.purchases || [];
    } catch (error) {
      console.error('Error loading purchase details:', error);
      purchaseDetails.value = [];
    } finally {
      isLoadingPurchaseDetails.value = false;
    }
  } else {
    purchaseDetails.value = [];
  }
}

function closePurchaseDetails() {
  isPurchaseDetailsOpen.value = false;
  selectedHolding.value = null;
  purchaseDetails.value = [];
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
  
  // Rafraîchir le portfolio toutes les 30 secondes pour mettre à jour les prix en temps réel
  portfolioRefreshTimer = setInterval(async () => {
    await loadPortfolio();
    // Mettre à jour les détails des achats si la modal est ouverte
    if (isPurchaseDetailsOpen.value && selectedHolding.value?.cryptoCurrencyId) {
      try {
        const response = await getPurchaseDetails(selectedHolding.value.cryptoCurrencyId);
        purchaseDetails.value = response.purchases || [];
      } catch (error) {
        console.error('Error refreshing purchase details:', error);
      }
    }
  }, 30000); // 30 secondes
});

onUnmounted(() => {
  if (portfolioRefreshTimer) {
    clearInterval(portfolioRefreshTimer);
  }
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
