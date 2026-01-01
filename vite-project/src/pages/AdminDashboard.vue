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
                            : 'bg-gradient-to-br from-green-600/30 to-green-800/30 group-hover:from-green-600/40 group-hover:to-green-800/40'
                        ]">
                          <component 
                            :is="payment.type === 'initialization' ? Wallet : CreditCard" 
                            :class="[
                              'h-6 w-6',
                              payment.type === 'initialization' ? 'text-blue-400' : 'text-green-400'
                            ]" 
                          />
                        </div>
                        <div class="flex-1 min-w-0">
                          <div class="flex items-center gap-3 mb-1">
                            <div class="font-semibold text-white">{{ payment.description }}</div>
                            <span :class="[
                              'px-2 py-0.5 rounded text-xs font-medium',
                              payment.status === 'completed' 
                                ? 'bg-green-600/20 text-green-400' 
                                : payment.status === 'pending' 
                                ? 'bg-yellow-600/20 text-yellow-400' 
                                : 'bg-red-600/20 text-red-400'
                            ]">
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
                        <div class="text-2xl font-bold text-green-400">{{ formatEUR(payment.amount) }}</div>
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
import { Wallet, TrendingUp, DollarSign, Activity, User, Shield, CreditCard, History, Plus, Calendar } from 'lucide-vue-next';
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
  
  // Add initialization payment if user exists and was approved
  if (auth.user) {
    const userCreatedAt = auth.user.created_at ? new Date(auth.user.created_at) : null;
    const initialBalance = 500.00;
    
    // Show initialization if user has balance >= 500€ (meaning they were approved by admin)
    if (auth.user.euro_balance && auth.user.euro_balance >= initialBalance && userCreatedAt) {
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
