<template>
  <div class="min-h-screen bg-gray-900 text-white">
    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold">Dashboard</h1>
          <p class="text-gray-400 mt-1 text-sm sm:text-base">Welcome to your dashboard</p>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div
          v-for="(stat, i) in stats"
          :key="i"
          class="stat-card group relative overflow-hidden"
        >
          <!-- Animated background gradient on hover -->
          <div class="absolute inset-0 bg-gradient-to-br from-blue-500/0 via-blue-500/0 to-blue-500/0 group-hover:from-blue-500/10 group-hover:via-blue-500/5 group-hover:to-transparent transition-all duration-500"></div>
          
          <!-- Border glow effect on hover -->
          <div class="absolute inset-0 rounded-xl border-2 border-transparent group-hover:border-blue-400/50 transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
          
          <div class="relative bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl p-6 border border-gray-700 transition-all duration-500 transform group-hover:scale-105 group-hover:shadow-2xl group-hover:shadow-blue-500/20">
            <div class="flex items-center justify-between mb-4">
              <!-- Icon with color variation -->
              <div 
                class="p-3 rounded-xl transition-all duration-500 group-hover:scale-110 group-hover:rotate-6"
                :style="getIconStyle(stat)"
              >
                <component :is="stat.icon" class="h-6 w-6" :style="{ color: getIconColor(stat) }" />
              </div>
              
              <!-- Change badge for Profit/Loss -->
              <div
                v-if="stat.title === 'Profit/Loss' && stat.value !== '€0.00'"
                :class="[
                  'text-xs font-medium px-2 py-1 rounded flex items-center gap-1',
                  parseFloat(stat.value.replace('€', '').replace(',', '')) >= 0 
                    ? 'bg-green-500/20 text-green-400 border border-green-500/30' 
                    : 'bg-red-500/20 text-red-400 border border-red-500/30'
                ]"
              >
                <component 
                  :is="parseFloat(stat.value.replace('€', '').replace(',', '')) >= 0 ? TrendingUp : TrendingDown" 
                  class="h-3 w-3" 
                />
                <span>{{ parseFloat(stat.value.replace('€', '').replace(',', '')) >= 0 ? '+' : '' }}</span>
              </div>
            </div>
            
            <div class="space-y-2">
              <div class="text-3xl sm:text-4xl font-bold text-white transition-all duration-300 group-hover:text-blue-300">
                {{ stat.value }}
              </div>
              <div class="text-gray-400 text-sm font-medium group-hover:text-gray-300 transition-colors duration-300">
                {{ stat.title }}
              </div>
            </div>
            
            <!-- Decorative element -->
            <div class="absolute bottom-0 right-0 w-20 h-20 opacity-5 group-hover:opacity-10 transition-opacity duration-500">
              <component :is="stat.icon" class="w-full h-full" :style="{ color: getIconColor(stat) }" />
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs Section -->
      <div class="bg-gray-800 rounded-xl p-4 sm:p-6 border border-gray-700">
        <div class="flex space-x-4 border-b border-gray-700 pb-4 mb-4 overflow-x-auto">
          <button 
            v-for="tab in tabs" 
            :key="tab.id" 
            @click="activeTab = tab.id" 
            :class="['px-3 py-2 rounded-md whitespace-nowrap transition-colors', activeTab === tab.id ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700']"
          >
            <span class="inline-flex items-center gap-2">
              <component :is="tab.icon" class="h-4 w-4" />
              {{ tab.label }}
            </span>
          </button>
        </div>

        <!-- Overview Tab -->
        <div v-if="activeTab === 'overview'" class="space-y-6">
          <!-- Portfolio Distribution Chart -->
          <div>
            <h3 class="text-lg font-semibold mb-3">Portfolio Distribution</h3>
            <div v-if="isLoadingPortfolio" class="flex justify-center items-center h-64">
              <div class="h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
            </div>
            <div v-else-if="pieChartData.series.length > 0" class="bg-gray-700/30 rounded-lg p-4">
              <ApexChart
                type="pie"
                height="350"
                :options="pieChartOptions"
                :series="pieChartData.series"
              />
            </div>
            <div v-else class="bg-gray-700/30 rounded-lg p-8 text-center text-gray-400">
              <p>No portfolio data available. Start trading to see your distribution.</p>
            </div>
          </div>

          <!-- Recent Transactions -->
          <div>
            <h3 class="text-lg font-semibold mb-4">Recent Transactions</h3>
            <div v-if="isLoadingTransactions" class="space-y-3">
              <div v-for="i in 2" :key="i" class="bg-gray-700/30 rounded-lg p-4 h-20 animate-pulse"></div>
            </div>
            <div v-else-if="recentTransactions.length > 0" class="space-y-3">
              <div 
                v-for="tx in recentTransactions" 
                :key="tx.id" 
                class="bg-gray-700/30 rounded-lg p-4 flex items-center justify-between hover:bg-gray-700/50 transition-colors border border-gray-700/50"
              >
                <div class="flex items-center gap-4 flex-1">
                  <div :class="[
                    'w-10 h-10 rounded-lg flex items-center justify-center'
                  ]" :style="tx.type === 'buy' ? { backgroundColor: 'rgba(1, 255, 25, 0.2)' } : { backgroundColor: 'rgba(255, 89, 100, 0.2)' }">
                    <component 
                      :is="tx.type === 'buy' ? TrendingUp : TrendingDown" 
                      class="h-5 w-5"
                      :style="{ color: tx.type === 'buy' ? '#01ff19' : '#ff5964' }"
                    />
                  </div>
                  <div class="flex-1">
                    <div class="font-semibold text-white mb-1">
                      <span :style="{ color: tx.type === 'buy' ? '#01ff19' : '#ff5964' }">
                        {{ tx.type.toUpperCase() }}
                      </span>
                      <span class="ml-2">{{ tx.portfolio?.crypto?.symbol || 'N/A' }}</span>
                    </div>
                    <div class="text-sm text-gray-400">{{ new Date(tx.created_at).toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' }) }}</div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="font-bold text-lg">{{ formatEUR(tx.euro_amount) }}</div>
                  <div class="text-sm text-gray-400 font-mono">{{ tx.quantity }} @ {{ formatEUR(tx.price_at_transaction) }}</div>
                </div>
              </div>
            </div>
            <div v-else class="bg-gray-700/30 rounded-lg p-8 text-center text-gray-400 border border-gray-700/50">
              <p>No transactions yet. Start trading to see your history.</p>
            </div>
          </div>

          <!-- Portfolio Holdings Table -->
          <div>
            <h3 class="text-lg font-semibold mb-4">Portfolio Holdings</h3>
            <div v-if="isLoadingPortfolio" class="bg-gray-700/30 rounded-lg p-8 border border-gray-700/50">
              <div class="flex justify-center">
                <div class="h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
              </div>
            </div>
            <div v-else-if="portfolioHoldings.length > 0" class="bg-gray-800/50 rounded-xl border border-gray-700/50 overflow-hidden">
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead class="bg-gray-700/50 border-b border-gray-700">
                    <tr>
                      <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Asset</th>
                      <th class="px-6 py-4 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">Quantity</th>
                      <th class="px-6 py-4 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">Avg Price</th>
                      <th class="px-6 py-4 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">Current Price</th>
                      <th class="px-6 py-4 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">Value (EUR)</th>
                      <th class="px-6 py-4 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">P&L</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-700/50">
                    <tr 
                      v-for="holding in portfolioHoldings" 
                      :key="holding.id"
                      class="hover:bg-gray-700/30 transition-colors"
                    >
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                          <div class="w-10 h-10 bg-gray-700 rounded-lg flex items-center justify-center flex-shrink-0 overflow-hidden" :data-symbol="holding.symbol">
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
                            <div class="font-semibold text-white">{{ holding.symbol }}</div>
                            <div class="text-sm text-gray-400">{{ holding.name }}</div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="font-mono font-semibold text-white">{{ holding.quantity.toFixed(8) }}</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="font-mono text-gray-300">{{ formatEUR(holding.avgPrice) }}</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="font-mono font-semibold text-white">{{ formatEUR(holding.currentPrice) }}</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="font-mono font-bold text-white">{{ formatEUR(holding.value) }}</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="flex items-center justify-end gap-2">
                          <component 
                            :is="holding.gainLoss >= 0 ? TrendingUp : TrendingDown" 
                            class="h-4 w-4"
                            :style="{ color: holding.gainLoss >= 0 ? '#01ff19' : '#ff5964' }"
                          />
                          <div>
                            <div class="font-bold font-mono" :style="{ color: holding.gainLoss >= 0 ? '#01ff19' : '#ff5964' }">
                              {{ holding.gainLoss >= 0 ? '+' : '' }}{{ formatEUR(holding.gainLoss) }}
                            </div>
                            <div class="text-xs font-medium" :style="{ color: holding.gainLossPercent >= 0 ? 'rgba(1, 255, 25, 0.8)' : 'rgba(255, 89, 100, 0.8)' }">
                              {{ holding.gainLossPercent >= 0 ? '+' : '' }}{{ holding.gainLossPercent.toFixed(2) }}%
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                  <tfoot class="bg-gray-700/50 border-t border-gray-700">
                    <tr>
                      <td colspan="4" class="px-6 py-4 text-right font-bold text-gray-300">Total Portfolio Value:</td>
                      <td class="px-6 py-4 text-right">
                        <div class="font-bold text-xl text-white font-mono">{{ formatEUR(totalPortfolioValue) }}</div>
                      </td>
                      <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                          <component 
                            :is="totalPortfolioGainLoss >= 0 ? TrendingUp : TrendingDown" 
                            class="h-4 w-4"
                            :style="{ color: totalPortfolioGainLoss >= 0 ? '#01ff19' : '#ff5964' }"
                          />
                          <div>
                            <div class="font-bold font-mono" :style="{ color: totalPortfolioGainLoss >= 0 ? '#01ff19' : '#ff5964' }">
                              {{ totalPortfolioGainLoss >= 0 ? '+' : '' }}{{ formatEUR(totalPortfolioGainLoss) }}
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
            <div v-else class="bg-gray-700/30 rounded-lg p-8 text-center text-gray-400 border border-gray-700/50">
              <p>No portfolio holdings. Start trading to build your portfolio.</p>
            </div>
          </div>
        </div>

        <!-- Security Tab -->
        <div v-else-if="activeTab === 'security'" class="space-y-6">
          <!-- Account Status -->
          <div class="bg-gray-700/30 rounded-xl border border-gray-700/50 p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
              <Shield class="h-5 w-5 text-blue-400" />
              Account Status
            </h3>
            <div class="space-y-4">
              <div class="flex items-center justify-between p-4 bg-gray-800/50 rounded-lg">
                <div>
                  <div class="font-medium text-white">Account Status</div>
                  <div class="text-sm text-gray-400 mt-1">
                    {{ accountStatusText }}
                  </div>
                </div>
                <div>
                  <span :class="[
                    'px-3 py-1 rounded-full text-sm font-medium',
                    auth.user?.status === 'pending' ? 'bg-yellow-600/20 text-yellow-400' : ''
                  ]" :style="auth.user?.status === 'active' 
                      ? { backgroundColor: 'rgba(1, 255, 25, 0.2)', color: '#01ff19' }
                      : auth.user?.status !== 'pending'
                      ? { backgroundColor: 'rgba(255, 89, 100, 0.2)', color: '#ff5964' }
                      : {}">
                    {{ auth.user?.status === 'active' ? 'Active' : auth.user?.status === 'pending' ? 'Pending Approval' : 'Blocked' }}
                  </span>
                </div>
              </div>
              <div v-if="auth.user?.status === 'pending'" class="p-4 bg-blue-600/10 border border-blue-600/30 rounded-lg">
                <div class="flex items-start gap-3">
                  <div class="w-5 h-5 rounded-full bg-blue-600/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="text-blue-400 text-xs">!</span>
                  </div>
                  <div class="flex-1">
                    <div class="font-medium text-blue-300 mb-1">Waiting for Admin Approval</div>
                    <div class="text-sm text-blue-400/80">
                      Your account is pending approval. Once approved by an administrator, you will receive an email notification and your account will be activated with an initial balance of 500€.
                    </div>
                  </div>
                </div>
              </div>
              <div v-if="auth.user?.must_change_password" class="p-4 bg-yellow-600/10 border border-yellow-600/30 rounded-lg">
                <div class="flex items-start gap-3">
                  <div class="w-5 h-5 rounded-full bg-yellow-600/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="text-yellow-400 text-xs">!</span>
                  </div>
                  <div class="flex-1">
                    <div class="font-medium text-yellow-300 mb-1">Password Change Required</div>
                    <div class="text-sm text-yellow-400/80">
                      You must change your temporary password. Please use the form below to set a new password.
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Change Password Form -->
          <div class="bg-gray-700/30 rounded-xl border border-gray-700/50 p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
              <Shield class="h-5 w-5 text-blue-400" />
              Change Password
            </h3>
            <div class="space-y-4 max-w-2xl">
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Current Password
                </label>
                <input
                  v-model="passwordForm.currentPassword"
                  type="password"
                  placeholder="Enter your current password"
                  class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  New Password
                </label>
                <input
                  v-model="passwordForm.newPassword"
                  type="password"
                  placeholder="Enter your new password (min. 6 characters)"
                  class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Confirm New Password
                </label>
                <input
                  v-model="passwordForm.confirmPassword"
                  type="password"
                  placeholder="Confirm your new password"
                  class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>

              <!-- Error Message -->
              <Transition name="slide-fade">
                <div v-if="passwordError" class="border-2 backdrop-blur-sm rounded-lg p-4" style="background-color: rgba(255, 89, 100, 0.15); border-color: rgba(255, 89, 100, 0.5);">
                  <div class="flex items-start gap-3">
                    <div class="w-2 h-2 rounded-full mt-2 flex-shrink-0" style="background-color: #ff5964;"></div>
                    <p class="text-sm font-medium" style="color: rgba(255, 89, 100, 0.9);">{{ passwordError }}</p>
                  </div>
                </div>
              </Transition>

              <!-- Success Message -->
              <Transition name="slide-fade">
                <div v-if="passwordSuccess" class="border-2 backdrop-blur-sm rounded-lg p-4" style="background-color: rgba(1, 255, 25, 0.15); border-color: rgba(1, 255, 25, 0.5);">
                  <div class="flex items-start gap-3">
                    <div class="w-2 h-2 rounded-full mt-2 flex-shrink-0" style="background-color: #01ff19;"></div>
                    <p class="text-sm font-medium" style="color: rgba(1, 255, 25, 0.9);">{{ passwordSuccess }}</p>
                  </div>
                </div>
              </Transition>

              <div class="flex gap-3 pt-2">
                <button
                  @click="handleChangePassword"
                  :disabled="isChangingPassword || !canChangePassword"
                  class="px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-lg transition-colors"
                >
                  {{ isChangingPassword ? 'Changing...' : 'Change Password' }}
                </button>
                <button
                  @click="resetPasswordForm"
                  class="px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-lg transition-colors"
                >
                  Cancel
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Payment Methods Tab -->
        <div v-else-if="activeTab === 'payment'" class="space-y-6">
          <!-- Unified Payment & History Section -->
          <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600/20 to-purple-600/20 border-b border-gray-700 px-6 py-4">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                    <CreditCard class="h-5 w-5 text-white" />
                  </div>
                  <div>
                    <h3 class="text-lg font-semibold">Payments & Deposits</h3>
                    <p class="text-sm text-gray-400">Manage your payment methods and view transaction history</p>
                  </div>
                </div>
                <button 
                  @click="showAddCardForm = !showAddCardForm"
                  class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg font-medium transition-colors flex items-center gap-2"
                >
                  <Plus class="h-4 w-4" />
                  Add Payment Method
                </button>
              </div>
            </div>

            <!-- Add Card Form (Collapsible) -->
            <div v-if="showAddCardForm" class="border-b border-gray-700 px-6 py-6 bg-gray-800/50">
              <div class="space-y-4 max-w-3xl">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm text-gray-300 mb-2">Card Number</label>
                    <div class="relative">
                      <CreditCard class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
                      <input
                        v-model="cardForm.cardNumber"
                        type="text"
                        placeholder="1234 5678 9012 3456"
                        maxlength="19"
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg pl-10 pr-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      />
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm text-gray-300 mb-2">Cardholder Name</label>
                    <input
                      v-model="cardForm.cardholderName"
                      type="text"
                      placeholder="John Doe"
                      class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>
                  <div>
                    <label class="block text-sm text-gray-300 mb-2">Expiry Date</label>
                    <input
                      v-model="cardForm.expiryDate"
                      type="text"
                      placeholder="MM/YY"
                      maxlength="5"
                      class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>
                  <div>
                    <label class="block text-sm text-gray-300 mb-2">CVV</label>
                    <input
                      v-model="cardForm.cvv"
                      type="text"
                      placeholder="123"
                      maxlength="4"
                      class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>
                </div>
                <div class="flex gap-3">
                  <button 
                    @click="handleAddCard"
                    :disabled="isAddingCard"
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg font-medium transition-colors"
                  >
                    {{ isAddingCard ? 'Adding...' : 'Add Card' }}
                  </button>
                  <button 
                    @click="showAddCardForm = false; resetCardForm()"
                    class="px-6 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg font-medium transition-colors"
                  >
                    Cancel
                  </button>
                </div>
              </div>
            </div>

            <!-- Payment History List -->
            <div class="divide-y divide-gray-700">
              <div v-if="isLoadingPayments" class="px-6 py-8">
                <div class="space-y-3">
                  <div v-for="i in 3" :key="i" class="h-20 bg-gray-700/30 rounded-lg animate-pulse"></div>
                </div>
              </div>
              <div v-else-if="paymentHistory.length > 0" class="px-6 py-4">
                <div class="space-y-3">
                  <div 
                    v-for="payment in paymentHistory" 
                    :key="payment.id" 
                    class="group relative bg-gray-700/20 hover:bg-gray-700/40 rounded-lg p-4 transition-all duration-200 border border-transparent hover:border-gray-600"
                  >
                    <div class="flex items-center justify-between">
                      <div class="flex items-center gap-4 flex-1">
                        <div :class="[
                          'w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 transition-all',
                          payment.type === 'initialization' 
                            ? 'bg-gradient-to-br from-blue-600/30 to-blue-800/30 group-hover:from-blue-600/40 group-hover:to-blue-800/40' 
                            : ''
                        ]" :style="payment.type === 'initialization' 
                          ? {}
                          : { background: 'linear-gradient(to bottom right, rgba(1, 255, 25, 0.3), rgba(1, 255, 25, 0.2))' }">
                          <component 
                            :is="payment.type === 'initialization' ? Wallet : CreditCard" 
                            class="h-6 w-6"
                            :style="{ color: payment.type === 'initialization' ? '#35a7ff' : '#01ff19' }" 
                          />
                        </div>
                        <div class="flex-1 min-w-0">
                          <div class="flex items-center gap-3 mb-1">
                            <div class="font-semibold text-white">{{ payment.description }}</div>
                            <span :class="[
                              'px-2 py-0.5 rounded text-xs font-medium',
                              payment.status === 'pending' ? 'bg-yellow-600/20 text-yellow-400' : ''
                            ]" :style="payment.status === 'completed' 
                                ? { backgroundColor: 'rgba(1, 255, 25, 0.2)', color: '#01ff19' }
                                : payment.status !== 'pending'
                                ? { backgroundColor: 'rgba(255, 89, 100, 0.2)', color: '#ff5964' }
                                : {}">
                              {{ payment.status === 'completed' ? 'Completed' : payment.status === 'pending' ? 'Pending' : 'Failed' }}
                            </span>
                          </div>
                          <div class="flex items-center gap-4 text-sm text-gray-400">
                            <div class="flex items-center gap-1.5">
                              <Calendar class="h-3.5 w-3.5" />
                              <span>{{ new Date(payment.date).toLocaleDateString('fr-FR', { year: 'numeric', month: 'short', day: 'numeric' }) }}</span>
                              <span>•</span>
                              <span>{{ new Date(payment.date).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }) }}</span>
                            </div>
                            <div v-if="payment.type === 'initialization'" class="flex items-center gap-1.5 text-blue-400">
                              <span class="text-xs">Initialized by admin</span>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="text-right ml-4">
                        <div class="text-2xl font-bold" style="color: #01ff19;">{{ formatEUR(payment.amount) }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">{{ payment.method || 'Deposit' }}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="px-6 py-12 text-center">
                <div class="w-16 h-16 bg-gray-700/30 rounded-full flex items-center justify-center mx-auto mb-4">
                  <CreditCard class="h-8 w-8 text-gray-500" />
                </div>
                <p class="text-gray-400 mb-2">No payment history available</p>
                <p class="text-sm text-gray-500">Add a payment method to get started</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Transaction History Tab -->
        <div v-else-if="activeTab === 'history'">
          <h3 class="text-lg font-semibold mb-3">Transaction History</h3>
          <div v-if="isLoadingTransactions" class="space-y-2">
            <div v-for="i in 5" :key="i" class="bg-gray-700/30 rounded-lg p-3 h-16 animate-pulse"></div>
          </div>
          <div v-else-if="transactions.length > 0" class="space-y-2">
            <div 
              v-for="tx in transactions" 
              :key="tx.id" 
              class="bg-gray-700/30 rounded-lg p-3 flex items-center justify-between hover:bg-gray-700/50 transition-colors"
            >
              <div>
                <div class="font-medium">
                  <span :style="{ color: tx.type === 'buy' ? '#01ff19' : '#ff5964' }">
                    {{ tx.type.toUpperCase() }}
                  </span>
                  {{ tx.portfolio?.crypto?.symbol || 'N/A' }}
                </div>
                <div class="text-sm text-gray-400">{{ new Date(tx.created_at).toLocaleDateString() }} {{ new Date(tx.created_at).toLocaleTimeString() }}</div>
              </div>
              <div class="text-right">
                <div class="font-semibold">{{ formatEUR(tx.euro_amount) }}</div>
                <div class="text-sm text-gray-400">{{ tx.quantity }} @ {{ formatEUR(tx.price_at_transaction) }}</div>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="flex items-center justify-between pt-4 border-t border-gray-700">
              <button
                @click="loadTransactions(pagination.current_page - 1)"
                :disabled="pagination.current_page === 1"
                class="px-4 py-2 bg-gray-700 rounded-lg hover:bg-gray-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Previous
              </button>
              <span class="text-sm text-gray-400">
                Page {{ pagination.current_page }} of {{ pagination.last_page }}
              </span>
              <button
                @click="loadTransactions(pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.last_page"
                class="px-4 py-2 bg-gray-700 rounded-lg hover:bg-gray-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Next
              </button>
            </div>
          </div>
          <div v-else class="bg-gray-700/30 rounded-lg p-8 text-center text-gray-400">
            <p>No transaction history available.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer - Full Width -->
    <FooterSection />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Wallet, TrendingUp, TrendingDown, Activity, User, Shield, CreditCard, History, Plus, Calendar, Euro } from 'lucide-vue-next';
import FooterSection from '../components/sectionsLanding/FooterSection.vue';
import { formatEUR } from '../utils/formatEUR';
import { useAuthStore } from '@/stores/auth';
import { getPortfolio, getTransactionHistory, changePassword } from '@/services/api';
import { getCryptoIcon } from '../utils/cryptoIcons';
import ApexChart from 'vue3-apexcharts';
import type { PortfolioResponse, Transaction, Paginated } from '@/types';

const auth = useAuthStore();
const activeTab = ref('overview');
const isLoadingPortfolio = ref(false);
const isLoadingTransactions = ref(false);
const portfolioData = ref<PortfolioResponse | null>(null);
const transactions = ref<Transaction[]>([]);
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0
});

const tabs = [
  { id: 'overview', label: 'Overview', icon: User },
  { id: 'security', label: 'Security', icon: Shield },
  { id: 'payment', label: 'Payment Methods', icon: CreditCard },
  { id: 'history', label: 'Transaction History', icon: History }
];

// Password change form
const passwordForm = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
});
const isChangingPassword = ref(false);
const passwordError = ref('');
const passwordSuccess = ref('');

const canChangePassword = computed(() => {
  return passwordForm.value.currentPassword.length > 0 
    && passwordForm.value.newPassword.length >= 6 
    && passwordForm.value.newPassword === passwordForm.value.confirmPassword;
});

const accountStatusText = computed(() => {
  if (auth.user?.status === 'active') {
    return 'Your account is active and ready to use.';
  } else if (auth.user?.status === 'pending') {
    return 'Your account is pending approval by an administrator.';
  } else {
    return 'Your account has been blocked. Please contact support.';
  }
});

function resetPasswordForm() {
  passwordForm.value = {
    currentPassword: '',
    newPassword: '',
    confirmPassword: ''
  };
  passwordError.value = '';
  passwordSuccess.value = '';
}

async function handleChangePassword() {
  if (!canChangePassword.value || isChangingPassword.value) return;
  
  passwordError.value = '';
  passwordSuccess.value = '';
  isChangingPassword.value = true;
  
  try {
    await changePassword({
      current_password: passwordForm.value.currentPassword,
      password: passwordForm.value.newPassword,
      password_confirmation: passwordForm.value.confirmPassword
    });
    
    passwordSuccess.value = 'Password changed successfully!';
    resetPasswordForm();
    
    // Clear success message after 3 seconds
    setTimeout(() => {
      passwordSuccess.value = '';
    }, 3000);
  } catch (error: any) {
    console.error('Password change error:', error);
    const errorMessage = error?.response?.data?.message || error?.message || 'Failed to change password. Please try again.';
    passwordError.value = errorMessage;
  } finally {
    isChangingPassword.value = false;
  }
}

// Payment form state
const showAddCardForm = ref(false);
const isAddingCard = ref(false);
const isLoadingPayments = ref(false);
const cardForm = ref({
  cardNumber: '',
  cardholderName: '',
  expiryDate: '',
  cvv: ''
});

function resetCardForm() {
  cardForm.value = {
    cardNumber: '',
    cardholderName: '',
    expiryDate: '',
    cvv: ''
  };
}

async function handleAddCard() {
  // Validation
  if (!cardForm.value.cardNumber || !cardForm.value.cardholderName || !cardForm.value.expiryDate || !cardForm.value.cvv) {
    return;
  }
  
  isAddingCard.value = true;
  try {
    // TODO: Add API call to save payment method
    // For now, just simulate success
    await new Promise(resolve => setTimeout(resolve, 1000));
    resetCardForm();
    showAddCardForm.value = false;
  } catch (error) {
    console.error('Error adding card:', error);
  } finally {
    isAddingCard.value = false;
  }
}

// Payment history - dynamically computed from user data
const paymentHistory = computed(() => {
  const history = [];
  
  // Add initialization payment if user exists and is active (was approved by admin)
  if (auth.user && auth.user.status === 'active') {
    const userCreatedAt = auth.user.created_at ? new Date(auth.user.created_at) : null;
    const initialBalance = 500.00;
    
    // Always show initialization for active users (approved by admin)
    if (userCreatedAt) {
      history.push({
        id: `init-${auth.user.id}`,
        type: 'initialization',
        description: 'Account Initialization Deposit',
        amount: initialBalance,
        date: userCreatedAt.toISOString(),
        status: 'completed',
        method: 'Admin Deposit'
      });
    }
  }
  
  // Sort by date (newest first)
  return history.sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());
});

// Get icon color based on card type
function getIconColor(stat: any): string {
  if (stat.title === 'Balance' || stat.title === 'Portfolio Value') return '#3b82f6';
  if (stat.title === 'Profit/Loss') {
    const value = parseFloat(stat.value.replace('€', '').replace(',', '')) || 0;
    return value >= 0 ? '#01ff19' : '#ff5964';
  }
  if (stat.title === 'Total Trades') return '#10b981';
  return '#9ca3af';
}

// Get icon background style
function getIconStyle(stat: any) {
  const color = getIconColor(stat);
  return {
    backgroundColor: `${color}20`,
    border: `1px solid ${color}40`,
  };
}

// Stats computed from real data
const stats = computed(() => {
  const balance = auth.user?.euro_balance ?? 0;
  const portfolioValue = portfolioData.value?.portfolio.reduce((sum, pos) => sum + (pos.current_value || 0), 0) ?? 0;
  const totalTrades = pagination.value.total || transactions.value.length;
  const totalPL = portfolioData.value?.portfolio.reduce((sum, pos) => {
    // Use gain_loss from backend if available, otherwise calculate it
    if (pos.gain_loss !== undefined && pos.gain_loss !== null) {
      return sum + Number(pos.gain_loss);
    }
    // Fallback calculation
    const currentValue = (pos.current_value || 0);
    const investedValue = (pos.total_invested_value || pos.invested_value || 0);
    return sum + (currentValue - investedValue);
  }, 0);

  return [
    { title: 'Balance', value: formatEUR(balance), icon: Wallet },
    { title: 'Total Trades', value: totalTrades.toString(), icon: TrendingUp },
    { title: 'Profit/Loss', value: formatEUR(totalPL), icon: Euro },
    { title: 'Portfolio Value', value: formatEUR(portfolioValue), icon: Activity },
  ];
});

// Recent transactions (last 2)
const recentTransactions = computed(() => transactions.value.slice(0, 2));

// Portfolio holdings for table
const portfolioHoldings = computed(() => {
  if (!portfolioData.value || !portfolioData.value.portfolio.length) {
    return [];
  }

  return portfolioData.value.portfolio
    .filter(pos => pos.quantity && pos.quantity > 0)
    .map(pos => {
      const quantity = pos.quantity || 0;
      const currentPrice = pos.current_price || 0;
      const currentValue = pos.current_value || (quantity * currentPrice);
      const totalInvestedValue = pos.total_invested_value || pos.invested_value || 0;
      const avgPrice = pos.average_purchase_price || (quantity > 0 ? totalInvestedValue / quantity : 0);
      
      // Use gain_loss from backend if available, otherwise calculate it
      const gainLoss = pos.gain_loss !== undefined && pos.gain_loss !== null 
        ? Number(pos.gain_loss)
        : currentValue - totalInvestedValue;
      
      // Use gain_loss_percent from backend if available, otherwise calculate it
      const gainLossPercent = pos.gain_loss_percent !== undefined && pos.gain_loss_percent !== null
        ? Number(pos.gain_loss_percent)
        : totalInvestedValue > 0 ? (gainLoss / totalInvestedValue) * 100 : 0;

      return {
        id: pos.id,
        symbol: pos.crypto?.symbol || 'N/A',
        name: pos.crypto?.name || 'Unknown',
        quantity,
        avgPrice,
        currentPrice,
        value: currentValue,
        gainLoss,
        gainLossPercent
      };
    })
    .sort((a, b) => b.value - a.value); // Sort by value descending
});

// Total portfolio value
const totalPortfolioValue = computed(() => {
  return portfolioHoldings.value.reduce((sum, holding) => sum + holding.value, 0);
});

// Total portfolio gain/loss
const totalPortfolioGainLoss = computed(() => {
  return portfolioHoldings.value.reduce((sum, holding) => sum + holding.gainLoss, 0);
});

// Pie chart data for portfolio distribution
const pieChartData = computed(() => {
  if (!portfolioData.value || !portfolioData.value.portfolio.length) {
    return { series: [], labels: [] };
  }

  const data = portfolioData.value.portfolio
    .filter(pos => pos.current_value && pos.current_value > 0)
    .map(pos => ({
      name: pos.crypto?.symbol || 'Unknown',
      value: pos.current_value || 0
    }));

  return {
    series: data.map(d => d.value),
    labels: data.map(d => d.name)
  };
});

const pieChartOptions = computed(() => ({
  chart: {
    type: 'pie' as const,
    background: 'transparent',
    toolbar: { show: false }
  },
  labels: pieChartData.value.labels,
  theme: {
    mode: 'dark' as const
  },
  colors: ['#3B82F6', '#01ff19', '#F59E0B', '#ff5964', '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16'],
  legend: {
    position: 'bottom' as const,
    labels: {
      colors: '#fff'
    }
  },
  dataLabels: {
    enabled: true,
    style: {
      colors: ['#fff']
    }
  },
  tooltip: {
    theme: 'dark',
    y: {
      formatter: (val: number) => formatEUR(val)
    }
  }
}));

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

async function loadTransactions(page = 1) {
  if (isLoadingTransactions.value) return;
  isLoadingTransactions.value = true;
  try {
    const response: Paginated<Transaction> = await getTransactionHistory({ page, per_page: 10 });
    transactions.value = response.data;
    pagination.value = {
      current_page: response.current_page,
      last_page: response.last_page,
      per_page: response.per_page,
      total: response.total
    };
  } catch (error) {
    console.error('Error loading transactions:', error);
  } finally {
    isLoadingTransactions.value = false;
  }
}

// Auto-refresh portfolio and transactions
let portfolioRefreshTimer: ReturnType<typeof setInterval> | null = null;

onMounted(async () => {
  if (!auth.user && auth.token) {
    await auth.fetchCurrentUser();
  }
  await Promise.all([loadPortfolio(), loadTransactions()]);
  
  // Refresh portfolio once per 24 hours to update profit/loss (86400000 ms)
  portfolioRefreshTimer = setInterval(() => {
    loadPortfolio();
  }, 86400000);
});

onUnmounted(() => {
  if (portfolioRefreshTimer) clearInterval(portfolioRefreshTimer);
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

/* Stat Card Styles */
.stat-card {
  cursor: pointer;
}
</style>
