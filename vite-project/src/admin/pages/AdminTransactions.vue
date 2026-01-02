<template>
  <div class="space-y-4 sm:space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-2">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-white to-gray-300 bg-clip-text text-transparent">
          Transaction History
        </h1>
        <p class="text-gray-400 mt-1 text-sm sm:text-base">View and manage all platform transactions</p>
      </div>
      <div class="flex items-center gap-3">
        <!-- Filters -->
        <select
          v-model="filters.type"
          @change="() => loadTransactions(1)"
          class="bg-gray-800/80 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all hover:bg-gray-800 text-sm"
        >
          <option value="">All Types</option>
          <option value="buy">Buy</option>
          <option value="sell">Sell</option>
        </select>
        <input
          v-model="filters.symbol"
          @input="debounceLoad"
          placeholder="Filter by symbol..."
          class="bg-gray-800/80 border border-gray-700 rounded-lg px-4 py-2.5 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all hover:bg-gray-800 text-sm w-40"
        />
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
    <div v-if="loading && transactions.length === 0" class="flex items-center justify-center py-12">
      <div class="text-gray-400">Loading transactions...</div>
    </div>

    <!-- Transactions Table -->
    <div v-else class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl border border-gray-700 shadow-lg overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-800/50 border-b border-gray-700">
            <tr>
              <th class="px-4 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">ID</th>
              <th class="px-4 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Type</th>
              <th class="px-4 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Crypto</th>
              <th class="px-4 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">User</th>
              <th class="px-4 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Quantity</th>
              <th class="px-4 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Price</th>
              <th class="px-4 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Amount</th>
              <th class="px-4 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Date</th>
              <th class="px-4 py-4 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-700">
            <tr
              v-for="transaction in transactions"
              :key="transaction.id"
              @click="openDetails(transaction)"
              class="hover:bg-gray-700/30 transition-all cursor-pointer group"
            >
              <td class="px-4 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-300">#{{ transaction.id }}</div>
              </td>
              <td class="px-4 py-4 whitespace-nowrap">
                <span
                  :class="[
                    'px-3 py-1.5 rounded-lg font-semibold text-xs uppercase inline-flex items-center gap-1.5',
                    transaction.type === 'buy'
                      ? 'bg-green-500/20 text-green-400 border border-green-500/30'
                      : 'bg-red-500/20 text-red-400 border border-red-500/30'
                  ]"
                >
                  <ArrowUpRight v-if="transaction.type === 'buy'" class="h-3 w-3" />
                  <ArrowDownRight v-else class="h-3 w-3" />
                  {{ transaction.type }}
                </span>
              </td>
              <td class="px-4 py-4 whitespace-nowrap">
                <div class="flex items-center gap-3">
                  <div class="relative w-10 h-10 rounded-xl bg-gray-700/50 flex items-center justify-center border border-gray-600/50 overflow-hidden group-hover:border-blue-500/50 transition-all">
                    <img
                      v-if="getCryptoIcon(transaction.portfolio?.crypto?.symbol || '')"
                      :src="getCryptoIcon(transaction.portfolio?.crypto?.symbol || '')"
                      :alt="transaction.portfolio?.crypto?.symbol || 'N/A'"
                      class="w-full h-full object-contain p-1.5"
                      @error="handleImageError($event)"
                    />
                    <span v-else class="text-xs font-bold text-white">
                      {{ transaction.portfolio?.crypto?.symbol?.charAt(0) || '?' }}
                    </span>
                  </div>
                  <div>
                    <div class="text-sm font-semibold text-white">
                      {{ transaction.portfolio?.crypto?.symbol || 'N/A' }}
                    </div>
                    <div class="text-xs text-gray-400">
                      {{ transaction.portfolio?.crypto?.name || '' }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-4 py-4 whitespace-nowrap">
                <div class="text-sm text-white font-medium">
                  {{ transaction.portfolio?.user?.name || 'Unknown' }}
                </div>
                <div class="text-xs text-gray-400">
                  {{ transaction.portfolio?.user?.email || '' }}
                </div>
              </td>
              <td class="px-4 py-4 whitespace-nowrap">
                <div class="text-sm text-white font-medium">
                  {{ formatNumber(transaction.quantity) }}
                </div>
              </td>
              <td class="px-4 py-4 whitespace-nowrap">
                <div class="text-sm text-white font-medium">
                  {{ formatCurrency(transaction.price_at_transaction) }}
                </div>
              </td>
              <td class="px-4 py-4 whitespace-nowrap">
                <div class="text-sm font-semibold text-white">
                  {{ formatCurrency(transaction.euro_amount) }}
                </div>
              </td>
              <td class="px-4 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-300">
                  {{ formatDateTime(transaction.created_at) }}
                </div>
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-center">
                <button
                  @click.stop="openDetails(transaction)"
                  class="p-2 text-gray-400 hover:text-blue-400 hover:bg-blue-500/10 rounded-lg transition-all opacity-0 group-hover:opacity-100"
                >
                  <Eye class="h-4 w-4" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && transactions.length === 0" class="p-12 text-center">
        <div class="mx-auto w-16 h-16 bg-gray-700/50 rounded-full flex items-center justify-center mb-4">
          <FileText class="h-8 w-8 text-gray-400" />
        </div>
        <h4 class="text-lg font-semibold text-white mb-2">No transactions found</h4>
        <p class="text-gray-400 text-sm">Try adjusting your filters</p>
      </div>

      <!-- Pagination -->
      <div v-if="pagination && pagination.last_page > 1" class="p-4 border-t border-gray-700 bg-gray-800/30 flex items-center justify-between">
        <div class="text-sm text-gray-400">
          Showing {{ (pagination.current_page - 1) * pagination.per_page + 1 }} to
          {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} of
          {{ pagination.total }} transactions
        </div>
        <div class="flex items-center gap-2">
          <button
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="px-3 py-2 bg-gray-700/50 hover:bg-gray-700 text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all text-sm"
          >
            Previous
          </button>
          <span class="text-sm text-gray-400">
            Page {{ pagination.current_page }} of {{ pagination.last_page }}
          </span>
          <button
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="px-3 py-2 bg-gray-700/50 hover:bg-gray-700 text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all text-sm"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Transaction Details Modal -->
    <Transition name="modal">
      <div
        v-if="selectedTransaction"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="closeDetails"
      >
        <!-- Backdrop with blur -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-md"></div>
        
        <!-- Modal Content -->
        <div class="relative z-10 bg-white/5 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col">
          <!-- Header -->
          <div class="px-6 py-5 border-b border-white/10 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div
                :class="[
                  'w-10 h-10 rounded-lg flex items-center justify-center',
                  selectedTransaction.type === 'buy'
                    ? 'bg-green-500/20 text-green-400'
                    : 'bg-red-500/20 text-red-400'
                ]"
              >
                <FileText class="h-5 w-5" />
              </div>
              <div>
                <h2 class="text-xl font-semibold text-white">Transaction Details</h2>
                <p class="text-xs text-gray-400 mt-0.5">ID: #{{ selectedTransaction.id }}</p>
              </div>
            </div>
            <button
              @click="closeDetails"
              class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-white hover:bg-white/10 rounded-lg transition-colors"
            >
              <X class="h-4 w-4" />
            </button>
          </div>
          
          <!-- Content -->
          <div class="overflow-y-auto flex-1">
            <div class="p-6 space-y-5">
              <!-- Type Badge -->
              <div class="flex justify-center">
                <div
                  :class="[
                    'px-4 py-2 rounded-lg text-sm font-medium',
                    selectedTransaction.type === 'buy'
                      ? 'bg-green-500/10 text-green-400 border border-green-500/20'
                      : 'bg-red-500/10 text-red-400 border border-red-500/20'
                  ]"
                >
                  {{ selectedTransaction.type.toUpperCase() }}
                </div>
              </div>

              <!-- Crypto Info -->
              <div class="space-y-3">
                <div class="text-xs font-medium text-gray-400 uppercase tracking-wider">Cryptocurrency</div>
                <div class="flex items-center gap-3 p-4 bg-white/5 rounded-xl border border-white/5">
                  <div class="w-12 h-12 rounded-lg bg-white/5 flex items-center justify-center border border-white/10 overflow-hidden">
                    <img
                      v-if="getCryptoIcon(selectedTransaction.portfolio?.crypto?.symbol || '')"
                      :src="getCryptoIcon(selectedTransaction.portfolio?.crypto?.symbol || '')"
                      :alt="selectedTransaction.portfolio?.crypto?.symbol || 'N/A'"
                      class="w-full h-full object-contain p-2"
                      @error="handleImageError($event)"
                    />
                    <span v-else class="text-sm font-semibold text-white">
                      {{ selectedTransaction.portfolio?.crypto?.symbol?.charAt(0) || '?' }}
                    </span>
                  </div>
                  <div>
                    <div class="text-lg font-semibold text-white">
                      {{ selectedTransaction.portfolio?.crypto?.symbol || 'N/A' }}
                    </div>
                    <div class="text-sm text-gray-400">
                      {{ selectedTransaction.portfolio?.crypto?.name || 'N/A' }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- User Info -->
              <div class="space-y-3">
                <div class="text-xs font-medium text-gray-400 uppercase tracking-wider">User</div>
                <div class="grid grid-cols-2 gap-3">
                  <div class="p-4 bg-white/5 rounded-xl border border-white/5">
                    <div class="text-xs text-gray-400 mb-1">Name</div>
                    <div class="text-sm font-medium text-white">
                      {{ selectedTransaction.portfolio?.user?.name || 'Unknown' }}
                    </div>
                  </div>
                  <div class="p-4 bg-white/5 rounded-xl border border-white/5">
                    <div class="text-xs text-gray-400 mb-1">Email</div>
                    <div class="text-sm font-medium text-white break-all">
                      {{ selectedTransaction.portfolio?.user?.email || 'N/A' }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Transaction Details -->
              <div class="space-y-3">
                <div class="text-xs font-medium text-gray-400 uppercase tracking-wider">Details</div>
                <div class="grid grid-cols-2 gap-3">
                  <div class="p-4 bg-white/5 rounded-xl border border-white/5">
                    <div class="text-xs text-gray-400 mb-1">Quantity</div>
                    <div class="text-base font-semibold text-white">
                      {{ formatNumber(selectedTransaction.quantity) }}
                    </div>
                  </div>
                  <div class="p-4 bg-white/5 rounded-xl border border-white/5">
                    <div class="text-xs text-gray-400 mb-1">Price per Unit</div>
                    <div class="text-base font-semibold text-white">
                      {{ formatCurrency(selectedTransaction.price_at_transaction) }}
                    </div>
                  </div>
                  <div class="p-4 bg-white/5 rounded-xl border border-white/5">
                    <div class="text-xs text-gray-400 mb-1">Date & Time</div>
                    <div class="text-sm font-medium text-white">
                      {{ formatDateTime(selectedTransaction.created_at) }}
                    </div>
                  </div>
                  <div class="p-4 bg-white/5 rounded-xl border border-white/5">
                    <div class="text-xs text-gray-400 mb-1">Portfolio ID</div>
                    <div class="text-sm font-medium text-white">
                      #{{ selectedTransaction.portfolio_id }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Total Amount -->
              <div class="pt-4 border-t border-white/10">
                <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl border border-white/5">
                  <div class="text-sm font-medium text-gray-400">Total Amount</div>
                  <div class="text-2xl font-bold text-white">
                    {{ formatCurrency(selectedTransaction.euro_amount) }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="px-6 py-4 border-t border-white/10 flex justify-end">
            <button
              @click="closeDetails"
              class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg text-sm font-medium transition-colors"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import {
  AlertCircle,
  Eye,
  ArrowUpRight,
  ArrowDownRight,
  FileText,
  X,
} from 'lucide-vue-next';
import { getAdminTransactions } from '@/services/api';
import { getCryptoIcon } from '@/utils/cryptoIcons';
import type { Transaction, Paginated } from '@/types';

const route = useRoute();
const loading = ref(false);
const errorMessage = ref('');
const transactions = ref<Transaction[]>([]);
const pagination = ref<Paginated<Transaction> | null>(null);
const selectedTransaction = ref<Transaction | null>(null);

const filters = ref({
  type: '' as '' | 'buy' | 'sell',
  symbol: '',
  user_id: undefined as number | undefined,
});

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

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

function formatDateTime(date: string | Date) {
  if (!date) return '';
  const d = typeof date === 'string' ? new Date(date) : date;
  return d.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  });
}

function debounceLoad() {
  if (debounceTimer) clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    loadTransactions();
  }, 500);
}

async function loadTransactions(page: number = 1) {
  loading.value = true;
  errorMessage.value = '';
  try {
    const params: any = {
      per_page: 25,
      page,
    };
    if (filters.value.type) params.type = filters.value.type;
    if (filters.value.symbol) params.symbol = filters.value.symbol;
    if (filters.value.user_id) params.user_id = filters.value.user_id;

    const data = await getAdminTransactions(params);
    transactions.value = data.data || [];
    pagination.value = data;

    // Check if we need to open a specific transaction from query params
    const transactionId = route.query.transactionId;
    if (transactionId && !selectedTransaction.value) {
      const tx = transactions.value.find((t) => t.id.toString() === transactionId);
      if (tx) {
        openDetails(tx);
      }
    }
  } catch (e: any) {
    errorMessage.value = e?.response?.data?.message || 'Unable to load transactions';
    console.error('Error loading transactions:', e);
  } finally {
    loading.value = false;
  }
}

function changePage(page: number) {
  if (pagination.value && page >= 1 && page <= pagination.value.last_page) {
    loadTransactions(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
}

function openDetails(transaction: Transaction) {
  selectedTransaction.value = transaction;
}

function handleImageError(event: Event) {
  const target = event.target as HTMLImageElement;
  if (target) {
    target.style.display = 'none';
  }
}

function closeDetails() {
  selectedTransaction.value = null;
  // Remove transactionId from URL if present
  if (route.query.transactionId) {
    window.history.replaceState({}, '', route.path);
  }
}

onMounted(() => {
  loadTransactions();
});
</script>

<style scoped>
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

/* Modal Transitions */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}

.modal-enter-active .relative.z-10,
.modal-leave-active .relative.z-10 {
  transition: transform 0.2s ease, opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .relative.z-10,
.modal-leave-to .relative.z-10 {
  transform: scale(0.95);
  opacity: 0;
}
</style>

