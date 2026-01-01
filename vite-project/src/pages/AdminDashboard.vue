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
        <div v-for="(stat, i) in stats" :key="i" class="bg-gray-800 rounded-xl p-6 border border-gray-700 hover:border-gray-600 transition-all">
          <div class="flex items-center justify-between mb-4">
            <div class="text-gray-400">
              <component :is="stat.icon" class="h-6 w-6" />
            </div>
          </div>
          <div class="space-y-1">
            <div class="text-2xl font-bold">{{ stat.value }}</div>
            <div class="text-gray-400 text-sm">{{ stat.title }}</div>
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

          <div>
            <h3 class="text-lg font-semibold mb-3">Recent Transactions</h3>
            <div v-if="isLoadingTransactions" class="space-y-3">
              <div v-for="i in 3" :key="i" class="bg-gray-700/30 rounded-lg p-3 h-16 animate-pulse"></div>
            </div>
            <div v-else-if="recentTransactions.length > 0" class="space-y-3">
              <div 
                v-for="tx in recentTransactions" 
                :key="tx.id" 
                class="bg-gray-700/30 rounded-lg p-3 flex items-center justify-between hover:bg-gray-700/50 transition-colors"
              >
                <div>
                  <div class="font-medium">
                    <span :class="tx.type === 'buy' ? 'text-green-400' : 'text-red-400'">
                      {{ tx.type.toUpperCase() }}
                    </span>
                    {{ tx.portfolio?.crypto?.symbol || 'N/A' }}
                  </div>
                  <div class="text-sm text-gray-400">{{ new Date(tx.created_at).toLocaleString() }}</div>
                </div>
                <div class="text-right">
                  <div class="font-semibold">{{ formatEUR(tx.euro_amount) }}</div>
                  <div class="text-sm text-gray-400">{{ tx.quantity }} @ {{ formatEUR(tx.price_at_transaction) }}</div>
                </div>
              </div>
            </div>
            <div v-else class="bg-gray-700/30 rounded-lg p-8 text-center text-gray-400">
              <p>No transactions yet. Start trading to see your history.</p>
            </div>
          </div>
        </div>

        <!-- Security Tab -->
        <div v-else-if="activeTab === 'security'">
          <h3 class="text-lg font-semibold mb-3">Security Settings</h3>
          <div class="space-y-3">
            <div 
              v-for="(s, i) in securitySettings" 
              :key="i" 
              class="bg-gray-700/30 rounded-lg p-3 flex items-start justify-between"
            >
              <div>
                <div class="font-medium">{{ s.name }}</div>
                <div class="text-sm text-gray-400">{{ s.description }}</div>
              </div>
              <div>
                <span :class="s.enabled ? 'text-green-400' : 'text-gray-400'">
                  {{ s.enabled ? 'Enabled' : 'Disabled' }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Payment Methods Tab -->
        <div v-else-if="activeTab === 'payment'">
          <h3 class="text-lg font-semibold mb-3">Payment Methods</h3>
          <div class="space-y-3">
            <div 
              v-for="pm in paymentMethods" 
              :key="pm.id" 
              class="bg-gray-700/30 rounded-lg p-3 flex items-center justify-between"
            >
              <div>
                <div class="font-medium">{{ pm.name }}</div>
                <div class="text-sm text-gray-400">{{ pm.type }}</div>
              </div>
              <div class="text-sm">
                <span class="mr-3" v-if="pm.verified">Verified</span>
                <button class="px-3 py-1 bg-gray-600 rounded text-sm hover:bg-gray-500 transition-colors">Manage</button>
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
                  <span :class="tx.type === 'buy' ? 'text-green-400' : 'text-red-400'">
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

      <!-- Quick Actions -->
      <!-- <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <router-link
            to="/trade"
            class="p-4 bg-gray-700 rounded-lg hover:bg-gray-600 transition-colors text-center"
          >
            Trade
          </router-link>
          <router-link
            to="/app/portfolio"
            class="p-4 bg-gray-700 rounded-lg hover:bg-gray-600 transition-colors text-center"
          >
            Portfolio
          </router-link>
          <router-link
            to="/profile"
            class="p-4 bg-gray-700 rounded-lg hover:bg-gray-600 transition-colors text-center"
          >
            Profile
          </router-link>
        </div>
      </div> -->
    </div>

    <!-- Footer - Full Width -->
    <FooterSection />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Wallet, TrendingUp, DollarSign, Activity, User, Shield, CreditCard, History } from 'lucide-vue-next';
import FooterSection from '../components/sectionsLanding/FooterSection.vue';
import { formatEUR } from '../utils/formatEUR';
import { useAuthStore } from '@/stores/auth';
import api, { getPortfolio, getTransactionHistory } from '@/services/api';
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

const securitySettings = [
  { name: 'Two-Factor Authentication', description: 'Add an extra layer of security to your account', enabled: true, critical: true },
  { name: 'Email Notifications', description: 'Receive alerts for important account activities', enabled: true, critical: false },
  { name: 'SMS Notifications', description: 'Get text messages for critical security events', enabled: false, critical: false },
  { name: 'Login Alerts', description: 'Get notified when someone logs into your account', enabled: true, critical: true }
];

const paymentMethods = [
  { id: '1', type: 'Bank Account', name: 'Chase ****1234', isDefault: true, verified: true },
  { id: '2', type: 'Credit Card', name: 'Visa ****5678', isDefault: false, verified: true },
  { id: '3', type: 'PayPal', name: 'john.doe@example.com', isDefault: false, verified: false }
];

// Stats computed from real data
const stats = computed(() => {
  const balance = auth.user?.euro_balance ?? 0;
  const portfolioValue = portfolioData.value?.portfolio.reduce((sum, pos) => sum + (pos.current_value || 0), 0) ?? 0;
  const totalTrades = pagination.value.total || transactions.value.length;
  const totalPL = portfolioData.value?.portfolio.reduce((sum, pos) => sum + (pos.gain_loss || 0), 0) ?? 0;

  return [
    { title: 'Balance', value: formatEUR(balance), icon: Wallet },
    { title: 'Total Trades', value: totalTrades.toString(), icon: TrendingUp },
    { title: 'Profit/Loss', value: formatEUR(totalPL), icon: DollarSign },
    { title: 'Portfolio Value', value: formatEUR(portfolioValue), icon: Activity },
  ];
});

// Recent transactions (last 5)
const recentTransactions = computed(() => transactions.value.slice(0, 5));

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
    type: 'pie',
    background: 'transparent',
    toolbar: { show: false }
  },
  labels: pieChartData.value.labels,
  theme: {
    mode: 'dark'
  },
  colors: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16'],
  legend: {
    position: 'bottom',
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

onMounted(async () => {
  if (!auth.user && auth.token) {
    await auth.fetchCurrentUser();
  }
  await Promise.all([loadPortfolio(), loadTransactions()]);
});
</script>

<style scoped>
/* Styles handled by Tailwind and ApexCharts */
</style>
