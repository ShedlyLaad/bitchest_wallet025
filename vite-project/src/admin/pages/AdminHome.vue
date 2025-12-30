<template>
  <div class="space-y-4 sm:space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-2">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-white to-gray-300 bg-clip-text text-transparent">Admin Dashboard</h1>
        <p class="text-gray-400 mt-1 text-sm sm:text-base">Monitor and manage your platform</p>
      </div>

      <div class="w-full sm:w-auto">
        <select v-model="timeFilter" :disabled="loading" class="w-full sm:w-auto bg-gray-800/80 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all hover:bg-gray-800">
          <option value="24h">Last 24 hours</option>
          <option value="7d">Last 7 days</option>
          <option value="30d">Last 30 days</option>
          <option value="90d">Last 90 days</option>
        </select>
      </div>
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
      <div v-for="(stat, i) in statCards" :key="i" class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl p-6 border border-gray-700 hover:border-gray-600 transition-all duration-300 transform hover:scale-[1.02] hover:shadow-xl group">
        <div class="flex items-center justify-between mb-4">
          <div class="p-2 rounded-lg bg-gray-700/50 group-hover:bg-gray-700 transition-colors">
            <component :is="stat.icon" class="h-6 w-6 text-gray-300" />
          </div>
          <div v-if="stat.change" :class="['text-sm font-medium px-2 py-1 rounded', stat.changeType === 'positive' ? 'PnL--pos bg-green-900/20' : 'PnL--neg bg-red-900/20']">{{ stat.change }}</div>
        </div>

        <div class="space-y-1">
          <div class="text-2xl sm:text-3xl font-bold text-white">
            <AnimatedCounter :value="stat.value" :prefix="stat.prefix || ''" :decimals="0" />
          </div>
          <div class="text-gray-400 text-sm font-medium">{{ stat.title }}</div>
        </div>
      </div>
    </div>

    <!-- Charts -->
    <div v-if="!loading || totals" class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
      <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl p-4 sm:p-6 border border-gray-700 shadow-lg hover:shadow-xl transition-all duration-300">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-2">
          <div>
            <h3 class="text-lg sm:text-xl font-semibold text-white">Revenue Overview</h3>
            <p class="text-xs text-gray-400 mt-1">Total revenue from sales</p>
          </div>
          <div class="px-3 py-1 bg-gray-700/50 rounded-lg text-sm text-gray-300 font-medium">{{ getTimeFilterLabel() }}</div>
        </div>
        <div v-if="totals && revenueSeries.length > 0" class="h-[250px] sm:h-[300px]">
          <CryptoChart :key="`revenue-${timeFilter}`" :series="revenueSeries" symbol="Revenue" mode="positive" type="area" height="100%" :currency="'EUR'" />
        </div>
        <div v-else-if="!loading" class="h-[250px] sm:h-[300px] flex items-center justify-center text-gray-500">
          <div class="text-center">
            <div class="text-sm">No revenue data available</div>
          </div>
        </div>
        <div v-else class="h-[250px] sm:h-[300px] flex items-center justify-center text-gray-400">
          <div class="text-center">
            <div class="text-sm animate-pulse">Loading chart...</div>
          </div>
        </div>
      </div>

      <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl p-4 sm:p-6 border border-gray-700 shadow-lg hover:shadow-xl transition-all duration-300">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-2">
          <div>
            <h3 class="text-lg sm:text-xl font-semibold text-white">Daily Trades</h3>
            <p class="text-xs text-gray-400 mt-1">Number of transactions</p>
          </div>
          <div class="px-3 py-1 bg-gray-700/50 rounded-lg text-sm text-gray-300 font-medium">{{ getTimeFilterLabel() }}</div>
        </div>
        <div v-if="totals && tradesSeries.length > 0" class="h-[250px] sm:h-[300px]">
          <CryptoChart :key="`trades-${timeFilter}`" :series="tradesSeries" symbol="Trades" mode="positive" type="area" height="100%" />
        </div>
        <div v-else-if="!loading" class="h-[250px] sm:h-[300px] flex items-center justify-center text-gray-500">
          <div class="text-center">
            <div class="text-sm">No trades data available</div>
          </div>
        </div>
        <div v-else class="h-[250px] sm:h-[300px] flex items-center justify-center text-gray-400">
          <div class="text-center">
            <div class="text-sm animate-pulse">Loading chart...</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
      <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl border border-gray-700 shadow-lg overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-700 bg-gray-800/50">
          <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
              <h3 class="text-lg sm:text-xl font-semibold text-white">Pending KYC Approvals</h3>
              <p class="text-xs text-gray-400 mt-1">Users awaiting validation</p>
            </div>
            <div class="flex items-center w-full sm:w-auto gap-2">
              <div class="relative flex-1 sm:flex-initial min-w-[200px]">
                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input v-model="searchTerm" placeholder="Search users..." class="w-full bg-gray-700/50 border border-gray-600 rounded-lg pl-10 pr-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" />
              </div>
              <button class="p-2 text-gray-400 hover:text-white hover:bg-gray-700/50 rounded-lg transition-colors">
                <Filter class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>

        <div class="divide-y divide-gray-700 overflow-x-auto">
          <div v-if="filteredPendingUsers.length === 0 && !loading" class="p-6 text-center text-gray-400">
            {{ searchTerm ? 'No users found matching your search' : 'No pending KYC approvals' }}
          </div>
          <div v-for="user in filteredPendingUsers" :key="user.id" class="p-4 sm:p-6 hover:bg-gray-700/30 transition-colors border-l-4 border-transparent hover:border-yellow-500">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
              <div class="space-y-1 flex-1">
                <div class="font-semibold text-white">{{ user.name }}</div>
                <div class="text-sm text-gray-400">{{ user.email }}</div>
                <div class="text-xs text-gray-500 mt-1">Submitted: {{ formatDate(user.submitDate) }}</div>
              </div>

              <div class="flex items-center gap-2">
                <button class="p-2 rounded-lg transition-all hover:scale-110 bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 hover:text-blue-300 border border-blue-600/30">
                  <Eye class="h-4 w-4" />
                </button>
                <button class="p-2 rounded-lg transition-all hover:scale-110 bg-green-600/20 hover:bg-green-600/30 text-green-400 hover:text-green-300 border border-green-600/30">
                  <CheckCircle class="h-4 w-4" />
                </button>
                <button class="p-2 rounded-lg transition-all hover:scale-110 bg-red-600/20 hover:bg-red-600/30 text-red-400 hover:text-red-300 border border-red-600/30">
                  <XCircle class="h-4 w-4" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl border border-gray-700 shadow-lg overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-700 bg-gray-800/50">
          <div>
            <h3 class="text-lg sm:text-xl font-semibold text-white">Recent Activity</h3>
            <p class="text-xs text-gray-400 mt-1">Latest transactions and actions</p>
          </div>
        </div>

        <div class="divide-y divide-gray-700">
          <div v-if="recentActivities.length === 0 && !loading" class="p-6 text-center text-gray-400">
            No recent activities
          </div>
          <div v-for="activity in recentActivities" :key="activity.id" class="p-4 sm:p-6 hover:bg-gray-700/30 transition-colors border-l-4 border-transparent hover:border-blue-500">
            <div class="flex items-start justify-between">
              <div class="space-y-1 flex-1">
                <div class="font-semibold text-sm text-white">{{ activity.user }}</div>
                <div class="text-gray-300 text-sm">{{ activity.action }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ formatDate(activity.time) }}</div>
              </div>
              <button class="p-1 text-gray-400 hover:text-white hover:bg-gray-700/50 rounded transition-colors"><MoreVertical class="h-4 w-4" /></button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { Search, Filter, Eye, CheckCircle, XCircle, MoreVertical, Users, TrendingUp, DollarSign, AlertCircle } from 'lucide-vue-next';
import CryptoChart from '@/components/CryptoChart.vue';
import AnimatedCounter from '@/components/AnimatedCounter.vue';
import api from '@/services/api';
import { useAuthStore } from '@/stores/auth';
const auth = useAuthStore();
const timeFilter = ref('7d');
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
const recentActivities = ref<{ id: number; user: string; action: string; time: string }[]>([]);

// Filtered pending users based on search term
const filteredPendingUsers = computed(() => {
  if (!searchTerm.value.trim()) {
    return pendingKycUsers.value;
  }
  const term = searchTerm.value.toLowerCase();
  return pendingKycUsers.value.filter(user => 
    user.name.toLowerCase().includes(term) || 
    user.email.toLowerCase().includes(term)
  );
});

onMounted(async () => {
  auth.hydrate?.();
  if (!auth.token) {
    errorMessage.value = 'Not authenticated as admin.';
    return;
  }
  await loadData();
});

// Watch timeFilter changes to reload data dynamically
watch(timeFilter, async () => {
  await loadData();
});

async function loadData() {
  loading.value = true;
  errorMessage.value = '';
  try {
    const data = await api.getAdminDashboard();
    totals.value = data.totals ?? null;
    revenueSeries.value = data.revenue_series || [];
    tradesSeries.value = data.trades_series || [];
    pendingKycUsers.value = data.pending_users || [];
    recentActivities.value = data.recent_activities || [];
  } catch (e: any) {
    errorMessage.value = e?.response?.data?.message || 'Unable to load admin data';
  } finally {
    loading.value = false;
  }
}

const isAdmin = computed(() => auth.user?.role === 'admin');

const statCards = computed(() => {
  const cards = [
    { title: 'Total Users', value: totals.value?.total_users ?? 0, change: '', changeType: 'positive', icon: Users },
    { title: 'Active Users', value: totals.value?.active_users ?? 0, change: '', changeType: 'positive', icon: TrendingUp },
    { title: 'Total Revenue', value: totals.value?.total_revenue ?? 0, change: '', changeType: 'positive', icon: DollarSign, prefix: '€' },
    { title: 'Pending Validation', value: totals.value?.pending_validation ?? 0, change: '', changeType: (totals.value?.pending_validation ?? 0) > 0 ? 'negative' : 'positive', icon: AlertCircle },
    { title: 'Trades', value: totals.value?.trades_count ?? 0, change: '', changeType: 'positive', icon: TrendingUp },
  ];
  
  // Hide EUR Balance for admin role
  if (!isAdmin.value) {
    cards.splice(4, 0, { title: 'EUR Balance', value: totals.value?.euro_balance ?? 0, change: '', changeType: 'positive', icon: DollarSign, prefix: '€' });
  }
  
  return cards;
});

function getTimeFilterLabel() {
  const labels: Record<string, string> = {
    '24h': 'Last 24 hours',
    '7d': 'Last 7 days',
    '30d': 'Last 30 days',
    '90d': 'Last 90 days'
  };
  return labels[timeFilter.value] || 'Monthly';
}

function formatDate(date: string | Date) {
  if (!date) return '';
  const d = typeof date === 'string' ? new Date(date) : date;
  return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}
</script>

<style scoped>
/* rely on Tailwind; small spacing handled inline */
</style>