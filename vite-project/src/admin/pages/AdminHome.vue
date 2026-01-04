<template>
  <div class="space-y-4 sm:space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-2">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-white to-gray-300 bg-clip-text text-transparent">
          Admin Dashboard
        </h1>
        <p class="text-gray-400 mt-1 text-sm sm:text-base">Monitor and manage your platform</p>
      </div>
      <select
        v-model="timeFilter"
        :disabled="loading"
        class="w-full sm:w-auto bg-gray-800/80 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all hover:bg-gray-800"
      >
        <option value="24h">Last 24 hours</option>
        <option value="7d">Last 7 days</option>
        <option value="30d">Last 30 days</option>
        <option value="90d">Last 90 days</option>
      </select>
    </div>

    <!-- Error Message -->
    <div v-if="errorMessage" class="bg-red-900/20 border-l-4 border-red-500 rounded-lg p-4 text-red-300 shadow-lg">
      <div class="flex items-center gap-2">
        <AlertCircle class="h-5 w-5 flex-shrink-0" />
        <span>{{ errorMessage }}</span>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading && !totals" class="flex items-center justify-center py-12">
      <div class="text-gray-400">Loading dashboard data...</div>
    </div>

    <!-- Stats Cards -->
    <div v-if="!loading || totals" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
      <div
        v-for="(stat, i) in statCards"
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
            
            <!-- Active indicator for Active Users -->
            <div
              v-if="stat.title === 'Active Users'"
              class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-green-500/20 border border-green-500/30"
            >
              <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
              <span class="text-xs font-medium text-green-400">Active</span>
            </div>
            
            <!-- Change badge -->
            <div
              v-else-if="stat.change"
              :class="['text-sm font-medium px-2 py-1 rounded', stat.changeType === 'positive' ? 'PnL--pos bg-green-900/20' : 'PnL--neg bg-red-900/20']"
            >
              {{ stat.change }}
            </div>
          </div>
          
          <div class="space-y-2">
            <div class="text-3xl sm:text-4xl font-bold text-white transition-all duration-300 group-hover:text-blue-300">
              <AnimatedCounter :value="stat.value" :prefix="stat.prefix || ''" :decimals="0" />
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

    <!-- Separate Charts Section -->
    <div v-if="!loading || totals" class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
      <!-- Revenue Chart -->
      <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl border border-gray-700 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-700/50 bg-gray-800/30">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 class="text-lg sm:text-xl font-semibold text-white">Revenue Overview</h3>
              <p class="text-xs text-gray-400 mt-1">{{ timeFilterLabel }}</p>
            </div>
            <div class="px-3 py-1.5 bg-gray-700/50 rounded-lg text-sm text-gray-300 font-medium border border-gray-600/50">
              <span class="text-base font-bold mr-1">€</span>
              Revenue
            </div>
          </div>
          
          <!-- Key Metrics -->
          <div v-if="totals && chartRevenueData.length > 0" class="grid grid-cols-3 gap-3 mb-4">
            <div class="bg-gray-700/30 rounded-lg p-3 border border-gray-600/30">
              <div class="text-xs text-gray-400 mb-1">Current</div>
              <div class="text-lg font-bold text-white">{{ formatCurrency(chartRevenueData[chartRevenueData.length - 1] || 0) }}</div>
            </div>
            <div class="bg-gray-700/30 rounded-lg p-3 border border-gray-600/30">
              <div class="text-xs text-gray-400 mb-1">Peak</div>
              <div class="text-lg font-bold text-green-400">{{ formatCurrency(Math.max(...chartRevenueData)) }}</div>
            </div>
            <div class="bg-gray-700/30 rounded-lg p-3 border border-gray-600/30">
              <div class="text-xs text-gray-400 mb-1">Average</div>
              <div class="text-lg font-bold text-blue-400">{{ formatCurrency(getAverage(chartRevenueData)) }}</div>
            </div>
          </div>
        </div>

        <!-- Revenue Line Chart -->
        <div v-if="chartRevenueData.length > 0" class="p-4 sm:p-6">
          <ApexChart
            :key="`revenue-chart-${timeFilter}`"
            :options="revenueChartOptions"
            :series="revenueChartSeries"
            type="line"
            height="300"
          />
        </div>
        <div v-else class="h-[300px] flex items-center justify-center">
          <div class="text-center text-gray-500 text-sm">
            <div>{{ loading ? 'Loading chart data...' : 'No revenue data available' }}</div>
          </div>
        </div>
      </div>

      <!-- Trades Chart -->
      <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl border border-gray-700 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-700/50 bg-gray-800/30">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 class="text-lg sm:text-xl font-semibold text-white">Daily Trades</h3>
              <p class="text-xs text-gray-400 mt-1">{{ timeFilterLabel }}</p>
            </div>
            <div class="px-3 py-1.5 bg-gray-700/50 rounded-lg text-sm text-gray-300 font-medium border border-gray-600/50">
              <TrendingUp class="h-4 w-4 inline mr-1" />
              Trades
            </div>
          </div>

          <!-- Key Metrics -->
          <div v-if="totals && chartTradesData.length > 0" class="grid grid-cols-3 gap-3 mb-4">
            <div class="bg-gray-700/30 rounded-lg p-3 border border-gray-600/30">
              <div class="text-xs text-gray-400 mb-1">Current</div>
              <div class="text-lg font-bold text-white">{{ Math.round(chartTradesData[chartTradesData.length - 1] || 0) }}</div>
            </div>
            <div class="bg-gray-700/30 rounded-lg p-3 border border-gray-600/30">
              <div class="text-xs text-gray-400 mb-1">Peak</div>
              <div class="text-lg font-bold text-blue-400">{{ Math.round(Math.max(...chartTradesData)) }}</div>
            </div>
            <div class="bg-gray-700/30 rounded-lg p-3 border border-gray-600/30">
              <div class="text-xs text-gray-400 mb-1">Total</div>
              <div class="text-lg font-bold text-purple-400">{{ Math.round(getTotal(chartTradesData)) }}</div>
            </div>
          </div>
        </div>

        <!-- Trades Bar Chart -->
        <div v-if="chartTradesData.length > 0" class="p-4 sm:p-6">
          <ApexChart
            :key="`trades-chart-${timeFilter}`"
            :options="tradesChartOptions"
            :series="tradesChartSeries"
            type="bar"
            height="300"
          />
        </div>
        <div v-else class="h-[300px] flex items-center justify-center">
          <div class="text-center text-gray-500 text-sm">
            <div>{{ loading ? 'Loading chart data...' : 'No trades data available' }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
      <!-- Pending KYC Approvals -->
      <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl border border-gray-700 shadow-lg overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-700 bg-gray-800/50">
          <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-yellow-500/20 rounded-lg">
                  <AlertCircle class="h-5 w-5 text-yellow-400" />
                </div>
                <div>
                  <h3 class="text-lg sm:text-xl font-semibold text-white">Pending KYC Approvals</h3>
                  <p class="text-xs text-gray-400 mt-1">Users awaiting validation</p>
                </div>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <div class="px-3 py-1.5 bg-yellow-500/20 rounded-lg border border-yellow-500/30">
                <span class="text-yellow-400 font-bold text-lg">{{ filteredPendingUsers.length }}</span>
                <span class="text-yellow-400/70 text-xs ml-1">pending</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Enhanced Content -->
        <div class="divide-y divide-gray-700 overflow-x-auto max-h-[500px] overflow-y-auto">
          <!-- Empty State with Info -->
          <div v-if="filteredPendingUsers.length === 0 && !loading" class="p-8">
            <div class="text-center">
              <div class="mx-auto w-16 h-16 bg-gray-700/50 rounded-full flex items-center justify-center mb-4">
                <CheckCircle class="h-8 w-8 text-green-400" />
              </div>
              <h4 class="text-lg font-semibold text-white mb-2">
                {{ searchTerm ? 'No users found' : 'All clear!' }}
              </h4>
              <p class="text-gray-400 text-sm mb-4">
                {{ searchTerm ? 'Try adjusting your search terms' : 'No pending KYC approvals at the moment' }}
              </p>
              <div v-if="!searchTerm" class="bg-gray-700/30 rounded-lg p-4 border border-gray-600/30">
                <div class="flex items-center justify-center gap-4 text-sm">
                  <div class="text-center">
                    <div class="text-2xl font-bold text-white">{{ totals?.total_users || 0 }}</div>
                    <div class="text-gray-400 text-xs mt-1">Total Users</div>
                  </div>
                  <div class="w-px h-12 bg-gray-600"></div>
                  <div class="text-center">
                    <div class="text-2xl font-bold text-green-400">{{ totals?.active_users || 0 }}</div>
                    <div class="text-gray-400 text-xs mt-1">Active Users</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Users List -->
          <div
            v-for="user in filteredPendingUsers"
            :key="user.id"
            class="p-4 sm:p-6 hover:bg-gray-700/30 transition-all border-l-4 border-yellow-500/50 hover:border-yellow-500 group"
          >
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
              <div class="flex items-start gap-3 flex-1">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-yellow-500/20 to-yellow-600/20 flex items-center justify-center border border-yellow-500/30 flex-shrink-0">
                  <span class="text-yellow-400 font-bold text-sm">{{ user.name.charAt(0).toUpperCase() }}</span>
                </div>
                <div class="space-y-1 flex-1 min-w-0">
                  <div class="font-semibold text-white">{{ user.name }}</div>
                  <div class="text-sm text-gray-400 truncate">{{ user.email }}</div>
                  <div class="flex items-center gap-2 mt-2">
                    <span class="text-xs text-gray-500">Submitted:</span>
                    <span class="text-xs text-yellow-400 font-medium">{{ formatDate(user.submitDate) }}</span>
                  </div>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <button
                  class="p-2 rounded-lg transition-all hover:scale-110 bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 hover:text-blue-300 border border-blue-600/30"
                  title="View Details"
                >
                  <Eye class="h-4 w-4" />
                </button>
                <button
                  class="p-2 rounded-lg transition-all hover:scale-110 bg-green-600/20 hover:bg-green-600/30 text-green-400 hover:text-green-300 border border-green-600/30"
                  title="Approve"
                >
                  <CheckCircle class="h-4 w-4" />
                </button>
                <button
                  class="p-2 rounded-lg transition-all hover:scale-110 bg-red-600/20 hover:bg-red-600/30 text-red-400 hover:text-red-300 border border-red-600/30"
                  title="Reject"
                >
                  <XCircle class="h-4 w-4" />
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Search Bar -->
        <div v-if="filteredPendingUsers.length > 0 || searchTerm" class="p-4 border-t border-gray-700 bg-gray-800/30">
          <div class="flex items-center gap-2">
            <div class="relative flex-1">
              <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
              <input
                v-model="searchTerm"
                placeholder="Search users by name or email..."
                class="w-full bg-gray-700/50 border border-gray-600 rounded-lg pl-10 pr-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm"
              />
            </div>
            <button
              v-if="searchTerm"
              @click="searchTerm = ''"
              class="p-2 text-gray-400 hover:text-white hover:bg-gray-700/50 rounded-lg transition-colors"
              title="Clear search"
            >
              <XCircle class="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- Recent Transactions -->
      <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl border border-gray-700 shadow-lg overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-700 bg-gray-800/50">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg sm:text-xl font-semibold text-white">Recent Transactions</h3>
              <p class="text-xs text-gray-400 mt-1">Last 5 transactions</p>
            </div>
            <router-link
              to="/admin/transactions"
              class="group relative px-4 py-2.5 bg-gradient-to-r from-blue-600/20 to-blue-500/20 hover:from-blue-600/30 hover:to-blue-500/30 text-blue-400 hover:text-blue-300 rounded-lg border border-blue-500/40 hover:border-blue-400/60 transition-all duration-300 text-sm font-semibold flex items-center gap-2 shadow-lg shadow-blue-500/10 hover:shadow-blue-500/20 hover:scale-105"
            >
              <span class="relative z-10 flex items-center gap-2">
                View All Transactions
                <TrendingUp class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" />
              </span>
              <div class="absolute inset-0 bg-gradient-to-r from-blue-500/0 to-blue-400/0 group-hover:from-blue-500/10 group-hover:to-blue-400/10 rounded-lg transition-all duration-300"></div>
            </router-link>
          </div>
        </div>
        <div class="divide-y divide-gray-700 max-h-[500px] overflow-y-auto">
          <div v-if="recentTransactions.length === 0 && !loading" class="p-6 text-center text-gray-400">
            No recent transactions
          </div>
          <div
            v-for="transaction in recentTransactions"
            :key="transaction.id"
            @click="openTransactionDetails(transaction)"
            class="p-4 sm:p-6 hover:bg-gray-700/30 transition-all cursor-pointer border-l-4 border-transparent hover:border-blue-500 group"
          >
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1 space-y-2">
                <div class="flex items-center gap-3">
                  <div
                    :class="[
                      'px-3 py-1.5 rounded-lg font-semibold text-xs uppercase',
                      transaction.type === 'buy'
                        ? 'bg-green-500/20 text-green-400 border border-green-500/30'
                        : 'bg-red-500/20 text-red-400 border border-red-500/30'
                    ]"
                  >
                    {{ transaction.type }}
                  </div>
                  <div class="relative w-10 h-10 rounded-xl bg-gray-700/50 flex items-center justify-center border border-gray-600/50 overflow-hidden group-hover:border-blue-500/50 transition-all">
                    <img
                      v-if="getCryptoIcon(transaction.portfolio?.crypto?.symbol || '')"
                      :src="getCryptoIcon(transaction.portfolio?.crypto?.symbol || '')"
                      :alt="transaction.portfolio?.crypto?.symbol || 'N/A'"
                      class="w-full h-full object-contain p-1.5"
                      @error="(e: any) => e.target.style.display = 'none'"
                    />
                    <span v-else class="text-xs font-bold text-white">
                      {{ transaction.portfolio?.crypto?.symbol?.charAt(0) || '?' }}
                    </span>
                  </div>
                  <div>
                    <div class="font-semibold text-sm text-white">
                      {{ transaction.portfolio?.crypto?.symbol || 'N/A' }}
                    </div>
                    <div class="text-gray-400 text-xs">
                      {{ transaction.portfolio?.crypto?.name || '' }}
                    </div>
                  </div>
                </div>
                <div class="grid grid-cols-2 gap-3 text-sm">
                  <div>
                    <span class="text-gray-400">User:</span>
                    <span class="text-white ml-2 font-medium">{{ transaction.portfolio?.user?.name || 'Unknown' }}</span>
                  </div>
                  <div>
                    <span class="text-gray-400">Amount:</span>
                    <span class="text-white ml-2 font-medium">{{ formatCurrency(transaction.euro_amount) }}</span>
                  </div>
                  <div>
                    <span class="text-gray-400">Quantity:</span>
                    <span class="text-white ml-2 font-medium">{{ formatNumber(transaction.quantity) }}</span>
                  </div>
                  <div>
                    <span class="text-gray-400">Price:</span>
                    <span class="text-white ml-2 font-medium">{{ formatCurrency(transaction.price_at_transaction) }}</span>
                  </div>
                </div>
                <div class="text-xs text-gray-500 mt-1">
                  {{ formatDateTime(transaction.created_at) }}
                </div>
              </div>
              <button class="p-2 text-gray-400 hover:text-white hover:bg-gray-700/50 rounded transition-colors opacity-0 group-hover:opacity-100">
                <Eye class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import {
  Search,
  Eye,
  CheckCircle,
  XCircle,
  Users,
  TrendingUp,
  AlertCircle,
  Coins,
} from 'lucide-vue-next';
import { useRouter } from 'vue-router';
import type { Transaction } from '@/types';
import ApexChart from 'vue3-apexcharts';
import type { ApexOptions } from 'apexcharts';
import AnimatedCounter from '@/components/AnimatedCounter.vue';
import { getAdminDashboard, getAdminTransactions } from '@/services/api';
import { useAuthStore } from '@/stores/auth';
import { getCryptoIcon } from '@/utils/cryptoIcons';

const auth = useAuthStore();
const router = useRouter();
const timeFilter = ref('30d'); // Default to 30 days
const searchTerm = ref('');
const loading = ref(false);
const errorMessage = ref('');

type Totals = {
  total_users: number;
  active_users: number;
  pending_validation: number;
  euro_balance: number;
  total_revenue: number;
  trades_count: number;
};

const totals = ref<Totals | null>(null);
const revenueSeries = ref<number[]>([]);
const tradesSeries = ref<number[]>([]);
const pendingKycUsers = ref<{ id: number; name: string; email: string; submitDate: string }[]>([]);
const recentTransactions = ref<Transaction[]>([]);
const selectedTransaction = ref<Transaction | null>(null);

const isAdmin = computed(() => auth.user?.role === 'admin');

const TIME_FILTER_LABELS: Record<string, string> = {
  '24h': 'Last 24 hours',
  '7d': 'Last 7 days',
  '30d': 'Last 30 days',
  '90d': 'Last 90 days',
};

const timeFilterLabel = computed(() => TIME_FILTER_LABELS[timeFilter.value] || 'Monthly');

const filteredPendingUsers = computed(() => {
  if (!searchTerm.value.trim()) return pendingKycUsers.value;
  const term = searchTerm.value.toLowerCase();
  return pendingKycUsers.value.filter(
    (user) => user.name.toLowerCase().includes(term) || user.email.toLowerCase().includes(term)
  );
});

// Chart colors from graphs
const CHART_GREEN = '#10b981';
const CHART_BLUE = '#3b82f6';

// Get icon color based on card type
function getIconColor(stat: any): string {
  if (stat.title === 'Active Users') return CHART_GREEN;
  if (stat.title === 'Total Revenue' || stat.title === 'EUR Balance') return CHART_GREEN;
  if (stat.title === 'Trades') return CHART_BLUE;
  return '#9ca3af';
}

// Get icon background style
function getIconStyle(stat: any) {
  if (stat.title === 'Active Users') {
    return {
      backgroundColor: `${CHART_GREEN}20`,
      border: `1px solid ${CHART_GREEN}40`,
    };
  }
  return {
    backgroundColor: 'rgba(156, 163, 175, 0.2)',
    border: '1px solid rgba(156, 163, 175, 0.3)',
  };
}

// Process chart data
const chartRevenueData = computed(() => {
  if (!revenueSeries.value || revenueSeries.value.length === 0) return [];
  const validData = revenueSeries.value.filter(v => typeof v === 'number' && !isNaN(v) && v >= 0);
  return validData.length > 0 ? validData : [];
});

const chartTradesData = computed(() => {
  if (!tradesSeries.value || tradesSeries.value.length === 0) return [];
  const validData = tradesSeries.value.filter(v => typeof v === 'number' && !isNaN(v) && v >= 0);
  return validData.length > 0 ? validData : [];
});

// Generate labels based on time filter
const chartLabels = computed(() => {
  const count = Math.max(chartRevenueData.value.length, chartTradesData.value.length);
  if (count === 0) return [];
  
  const labels: string[] = [];
  const now = new Date();
  
  if (timeFilter.value === '24h') {
    for (let i = count - 1; i >= 0; i--) {
      const date = new Date(now);
      date.setHours(date.getHours() - i);
      labels.push(date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false }));
    }
  } else {
    for (let i = count - 1; i >= 0; i--) {
      const date = new Date(now);
      date.setDate(date.getDate() - i);
      labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
    }
  }
  
  return labels;
});

// Revenue Chart Series (Line)
const revenueChartSeries = computed(() => [
  {
    name: 'Revenue',
    data: chartRevenueData.value,
  },
]);

// Revenue Chart Options
const revenueChartOptions = computed(() => ({
  chart: {
    type: 'line' as const,
    height: 300,
    background: 'transparent',
    toolbar: {
      show: false,
    },
    zoom: {
      enabled: false,
    },
  },
  stroke: {
    curve: 'smooth' as const,
    width: 3,
    colors: ['#10b981'],
  },
  colors: ['#10b981'],
  dataLabels: {
    enabled: false,
  },
  legend: {
    show: false,
  },
  xaxis: {
    categories: chartLabels.value,
    labels: {
      style: {
        colors: '#9ca3af',
        fontSize: '12px',
      },
      rotate: timeFilter.value === '90d' ? -45 : 0,
    },
    axisBorder: {
      color: '#374151',
    },
    axisTicks: {
      color: '#374151',
    },
  },
  yaxis: {
    title: {
      text: 'Revenue (€)',
      style: {
        color: '#10b981',
        fontSize: '12px',
        fontWeight: 600,
      },
    },
    labels: {
      style: {
        colors: '#10b981',
      },
      formatter: (val: number) => {
        if (val >= 1000) {
          return `€${(val / 1000).toFixed(1)}K`;
        }
        return `€${val.toFixed(0)}`;
      },
    },
  },
  grid: {
    borderColor: '#374151',
    strokeDashArray: 3,
    xaxis: {
      lines: {
        show: true,
      },
    },
    yaxis: {
      lines: {
        show: true,
      },
    },
  },
  tooltip: {
    theme: 'dark',
    style: {
      fontSize: '12px',
    },
    y: {
      formatter: (val: number) => formatCurrency(val),
    },
  },
  fill: {
    type: 'gradient',
    gradient: {
      shade: 'dark',
      type: 'vertical',
      shadeIntensity: 0.3,
      gradientToColors: ['#10b981'],
      inverseColors: false,
      opacityFrom: 0.7,
      opacityTo: 0.1,
      stops: [0, 100],
    },
  },
  theme: {
    mode: 'dark' as const,
  },
} as ApexOptions));

// Trades Chart Series (Bar)
const tradesChartSeries = computed(() => [
  {
    name: 'Trades',
    data: chartTradesData.value,
  },
]);

// Trades Chart Options
const tradesChartOptions = computed(() => ({
  chart: {
    type: 'bar' as const,
    height: 300,
    background: 'transparent',
    toolbar: {
      show: false,
    },
    zoom: {
      enabled: false,
    },
  },
  plotOptions: {
    bar: {
      columnWidth: '60%',
      borderRadius: 4,
    },
  },
  colors: ['#3b82f6'],
  dataLabels: {
    enabled: false,
  },
  legend: {
    show: false,
  },
  xaxis: {
    categories: chartLabels.value,
    labels: {
      style: {
        colors: '#9ca3af',
        fontSize: '12px',
      },
      rotate: timeFilter.value === '90d' ? -45 : 0,
    },
    axisBorder: {
      color: '#374151',
    },
    axisTicks: {
      color: '#374151',
    },
  },
  yaxis: {
    title: {
      text: 'Trades',
      style: {
        color: '#3b82f6',
        fontSize: '12px',
        fontWeight: 600,
      },
    },
    labels: {
      style: {
        colors: '#3b82f6',
      },
      formatter: (val: number) => Math.round(val).toString(),
    },
  },
  grid: {
    borderColor: '#374151',
    strokeDashArray: 3,
    xaxis: {
      lines: {
        show: true,
      },
    },
    yaxis: {
      lines: {
        show: true,
      },
    },
  },
  tooltip: {
    theme: 'dark',
    style: {
      fontSize: '12px',
    },
    y: {
      formatter: (val: number) => `${Math.round(val)} trades`,
    },
  },
  fill: {
    type: 'gradient',
    gradient: {
      shade: 'dark',
      type: 'vertical',
      shadeIntensity: 0.5,
      gradientToColors: ['#60a5fa'],
      inverseColors: false,
      opacityFrom: 0.8,
      opacityTo: 0.3,
      stops: [0, 100],
    },
  },
  theme: {
    mode: 'dark' as const,
  },
} as ApexOptions));

const statCards = computed(() => {
  const cards = [
    { title: 'Total Users', value: totals.value?.total_users ?? 0, change: '', changeType: 'positive', icon: Users },
    {
      title: 'Active Users',
      value: totals.value?.active_users ?? 0,
      change: '',
      changeType: 'positive',
      icon: TrendingUp,
    },
    {
      title: 'Total Revenue',
      value: totals.value?.total_revenue ?? 0,
      change: '',
      changeType: 'positive',
      icon: Coins,
      prefix: '€',
    },
    {
      title: 'Pending Validation',
      value: totals.value?.pending_validation ?? 0,
      change: '',
      changeType: (totals.value?.pending_validation ?? 0) > 0 ? 'negative' : 'positive',
      icon: AlertCircle,
    },
    { title: 'Trades', value: totals.value?.trades_count ?? 0, change: '', changeType: 'positive', icon: TrendingUp },
  ];
  if (!isAdmin.value) {
    cards.splice(4, 0, {
      title: 'EUR Balance',
      value: totals.value?.euro_balance ?? 0,
      change: '',
      changeType: 'positive',
      icon: Coins,
      prefix: '€',
    });
  }
  return cards;
});

function formatDate(date: string | Date) {
  if (!date) return '';
  const d = typeof date === 'string' ? new Date(date) : date;
  return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatDateTime(date: string | Date) {
  if (!date) return '';
  const d = typeof date === 'string' ? new Date(date) : date;
  return d.toLocaleString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

function formatCurrency(value: number) {
  return new Intl.NumberFormat('en-GB', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value);
}

function formatNumber(value: number, decimals: number = 8) {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: decimals,
  }).format(value);
}

function openTransactionDetails(transaction: Transaction) {
  selectedTransaction.value = transaction;
  // Navigate to transactions page with the transaction ID
  router.push({ 
    name: 'AdminTransactions', 
    query: { transactionId: transaction.id.toString() } 
  });
}

function getAverage(series: number[]) {
  if (series.length === 0) return 0;
  return series.reduce((a, b) => a + b, 0) / series.length;
}

function getTotal(series: number[]) {
  return series.reduce((a, b) => a + b, 0);
}

async function loadData() {
  loading.value = true;
  errorMessage.value = '';
  try {
    const data = await getAdminDashboard(timeFilter.value);
    totals.value = data.totals ?? null;
    
    revenueSeries.value = Array.isArray(data.revenue_series) 
      ? data.revenue_series.map(v => typeof v === 'number' ? v : parseFloat(v) || 0)
      : [];
    tradesSeries.value = Array.isArray(data.trades_series)
      ? data.trades_series.map(v => typeof v === 'number' ? v : parseFloat(v) || 0)
      : [];
    
    pendingKycUsers.value = data.pending_users || [];
    
    // Fetch the last 5 transactions with full details
    try {
      const txData = await getAdminTransactions({ per_page: 5, page: 1 });
      recentTransactions.value = txData.data || [];
    } catch (e) {
      console.error('Error fetching recent transactions:', e);
      recentTransactions.value = [];
    }
  } catch (e: any) {
    errorMessage.value = e?.response?.data?.message || 'Unable to load admin data';
    console.error('Error loading dashboard data:', e);
  } finally {
    loading.value = false;
  }
}

onMounted(async () => {
  auth.hydrate?.();
  if (!auth.token) {
    errorMessage.value = 'Not authenticated as admin.';
    return;
  }
  
  // Ensure user is fetched if not available
  if (!auth.user && auth.token) {
    try {
      await auth.fetchCurrentUser();
    } catch (e) {
      console.error('Failed to fetch user:', e);
      errorMessage.value = 'Failed to authenticate. Please login again.';
      return;
    }
  }
  
  // Verify user is admin
  if (auth.user?.role !== 'admin') {
    errorMessage.value = 'Access denied. Admin privileges required.';
    return;
  }
  
  await loadData();
});

watch(timeFilter, loadData);
</script>

<style scoped>
/* Stat Card Styles */
.stat-card {
  cursor: pointer;
}

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
