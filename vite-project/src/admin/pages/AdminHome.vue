<template>
  <div class="space-y-4 sm:space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold">Admin Dashboard</h1>
        <p class="text-gray-400 mt-1 text-sm sm:text-base">Monitor and manage your platform</p>
      </div>

      <div class="w-full sm:w-auto">
        <select v-model="timeFilter" class="w-full sm:w-auto bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2">
          <option value="24h">Last 24 hours</option>
          <option value="7d">Last 7 days</option>
          <option value="30d">Last 30 days</option>
          <option value="90d">Last 90 days</option>
        </select>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-6">
      <div v-for="(stat, i) in statCards" :key="i" class="bg-gray-800 rounded-xl p-6 border border-gray-700 hover:border-gray-600 transition-all duration-300 transform hover:scale-105">
        <div class="flex items-center justify-between mb-4">
          <div class="text-gray-400"><component :is="stat.icon" class="h-6 w-6" /></div>
          <div :class="['text-sm font-medium', stat.changeType === 'positive' ? 'PnL--pos' : 'PnL--neg']">{{ stat.change }}</div>
        </div>

        <div class="space-y-1">
          <div class="text-2xl font-bold">
            <AnimatedCounter :value="stat.value" :prefix="stat.prefix || ''" :decimals="0" />
          </div>
          <div class="text-gray-400 text-sm">{{ stat.title }}</div>
        </div>
      </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-6">
      <div class="bg-gray-800 rounded-xl p-4 sm:p-6 border border-gray-700">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-2">
          <h3 class="text-lg sm:text-xl font-semibold">Revenue Overview</h3>
          <div class="text-sm text-gray-400">Monthly</div>
        </div>
        <div class="h-[250px] sm:h-[300px]">
          <CryptoChart :series="revenueSeries" symbol="Revenue" mode="positive" type="area" height="100%" :currency="'EUR'" />
        </div>
      </div>

      <div class="bg-gray-800 rounded-xl p-4 sm:p-6 border border-gray-700">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-2">
          <h3 class="text-lg sm:text-xl font-semibold">Daily Trades</h3>
          <div class="text-sm text-gray-400">This Week</div>
        </div>
        <div class="h-[250px] sm:h-[300px]">
          <CryptoChart :series="tradesSeries" symbol="Trades" mode="positive" type="area" height="100%" />
        </div>
      </div>
    </div>

    <!-- Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-6">
      <div class="bg-gray-800 rounded-xl border border-gray-700">
        <div class="p-4 sm:p-6 border-b border-gray-700">
          <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <h3 class="text-lg sm:text-xl font-semibold">Pending KYC Approvals</h3>
            <div class="flex items-center w-full sm:w-auto gap-2">
              <div class="relative flex-1 sm:flex-initial">
                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input v-model="searchTerm" placeholder="Search users..." class="w-full bg-gray-700 border border-gray-600 rounded-lg pl-10 pr-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2" />
              </div>
              <button class="p-2 text-gray-400 hover:text-white">
                <Filter class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>

        <div class="divide-y divide-gray-700 overflow-x-auto">
          <div v-for="user in pendingKycUsers" :key="user.id" class="p-4 sm:p-6 hover:bg-gray-700/30 transition-colors">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
              <div class="space-y-1">
                <div class="font-semibold">{{ user.name }}</div>
                <div class="text-sm text-gray-400">{{ user.email }}</div>
                <div class="text-xs text-gray-500">Submitted: {{ user.submitDate }}</div>
              </div>

              <div class="flex items-center gap-2">
                <button class="p-2 rounded-lg transition-colors" :style="{ backgroundColor: 'var(--blue)', color: 'var(--bg)' }">
                  <Eye class="h-4 w-4" />
                </button>
                <button class="p-2 rounded-lg transition-colors" :style="{ backgroundColor: 'var(--accent-green)', color: 'var(--bg)' }">
                  <CheckCircle class="h-4 w-4" />
                </button>
                <button class="p-2 rounded-lg transition-colors" :style="{ backgroundColor: 'var(--accent-red)', color: 'var(--bg)' }">
                  <XCircle class="h-4 w-4" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-gray-800 rounded-xl border border-gray-700">
        <div class="p-6 border-b border-gray-700">
          <h3 class="text-xl font-semibold">Recent Activity</h3>
        </div>

        <div class="divide-y divide-gray-700">
          <div v-for="activity in recentActivities" :key="activity.id" class="p-6 hover:bg-gray-700/30 transition-colors">
            <div class="flex items-start justify-between">
              <div class="space-y-1">
                <div class="font-semibold text-sm">{{ activity.user }}</div>
                <div class="text-gray-300">{{ activity.action }}</div>
                <div class="text-xs text-gray-500">{{ activity.time }}</div>
              </div>
              <button class="p-1 text-gray-400 hover:text-white"><MoreVertical class="h-4 w-4" /></button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
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

onMounted(async () => {
  auth.hydrate?.();
  if (!auth.token) {
    errorMessage.value = 'Not authenticated as admin.';
    return;
  }
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

const statCards = computed(() => [
  { title: 'Total Users', value: totals.value?.total_users ?? 0, change: '', changeType: 'positive', icon: Users },
  { title: 'Active Users', value: totals.value?.active_users ?? 0, change: '', changeType: 'positive', icon: TrendingUp },
  { title: 'Total Revenue', value: totals.value?.total_revenue ?? 0, change: '', changeType: 'positive', icon: DollarSign, prefix: '€' },
  { title: 'Pending Validation', value: totals.value?.pending_validation ?? 0, change: '', changeType: (totals.value?.pending_validation ?? 0) > 0 ? 'negative' : 'positive', icon: AlertCircle },
  { title: 'EUR Balance', value: totals.value?.euro_balance ?? 0, change: '', changeType: 'positive', icon: DollarSign, prefix: '€' },
  { title: 'Trades', value: totals.value?.trades_count ?? 0, change: '', changeType: 'positive', icon: TrendingUp },
]);
</script>

<style scoped>
/* rely on Tailwind; small spacing handled inline */
</style>