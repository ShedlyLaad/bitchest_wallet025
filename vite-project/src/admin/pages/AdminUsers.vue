<template>
  <div class="space-y-4 sm:space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-white to-gray-300 bg-clip-text text-transparent">Users Management</h1>
        <p class="text-gray-400 mt-1 text-sm sm:text-base">Manage platform users and their activities</p>
      </div>

      <button
        @click="openCreateModal"
        class="flex items-center space-x-2 px-4 py-2 rounded-lg text-white transition-all hover:scale-105 shadow-lg shadow-blue-500/20 font-medium"
        :style="{ backgroundColor: 'var(--blue)' }"
      >
        <Plus class="h-4 w-4" />
        <span>Create User</span>
      </button>
    </div>

    <!-- Success Message -->
    <div v-if="createdTempPassword" class="bg-gray-800 rounded-xl p-6 border border-gray-700">
      <div class="flex items-start space-x-3">
        <Check class="h-5 w-5 mt-0.5" :style="{ color: 'var(--accent-green)' }" />
        <div class="flex-1">
          <h3 class="font-semibold text-white mb-2">User Created Successfully</h3>
          <p class="text-gray-300 text-sm mb-4">
            A temporary password has been generated. Share it with the user. They can change it in their private area.
          </p>
          <p class="text-gray-300 text-sm mb-4">
            For the prototype phase, the new account is credited with €500.
          </p>

          <div class="bg-gray-900 rounded-lg p-4 border border-gray-700">
            <div class="flex items-center justify-between">
              <code class="text-gray-300 font-mono text-sm">{{ maskedPassword }}</code>
              <button @click="handleCopyPassword" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-white text-sm" :style="{ backgroundColor: 'var(--blue-dark)' }">
                <template v-if="copiedPassword">
                  <Check class="h-4 w-4" />
                  <span>Copied!</span>
                </template>
                <template v-else>
                  <Copy class="h-4 w-4" />
                  <span>Copy</span>
                </template>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-else-if="successMessage" class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-sm text-green-400">
      {{ successMessage }}
    </div>

    <!-- Users Table -->
    <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl border border-gray-700 shadow-lg overflow-hidden">
      <div class="p-4 border-b border-gray-700 bg-gray-800/50">
        <h3 class="text-lg font-semibold text-white">Users List</h3>
        <p class="text-xs text-gray-400 mt-1">{{ users.length }} total users</p>
      </div>
      <div v-if="errorMessage" class="px-4 py-3 text-sm text-red-400 border-b border-gray-700 bg-red-900/10">{{ errorMessage }}</div>
      <div v-else-if="loading" class="px-4 py-3 text-sm text-gray-400 border-b border-gray-700">Loading users...</div>
      <div v-else-if="successMessage" class="px-4 py-3 text-sm text-green-400 border-b border-gray-700 bg-green-900/10">Nouvelle création : email envoyé.</div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-800/70 sticky top-0">
            <tr class="border-b border-gray-700">
              <th class="text-left p-4 text-xs text-gray-400 font-medium uppercase tracking-wider">Email</th>
              <th class="text-left p-4 text-xs text-gray-400 font-medium uppercase tracking-wider">Role</th>
              <th class="text-left p-4 text-xs text-gray-400 font-medium uppercase tracking-wider">Status</th>
              <th class="text-left p-4 text-xs text-gray-400 font-medium uppercase tracking-wider">Created At</th>
              <th class="text-right p-4 text-xs text-gray-400 font-medium uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="user in users" 
              :key="user.id" 
              @click="openUserDetails(user.id)"
              class="border-b border-gray-700/50 hover:bg-gray-700/30 transition-all duration-200 cursor-pointer group"
            >
              <td class="p-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-gradient-to-br from-blue-500/20 to-blue-600/20 rounded-lg flex items-center justify-center border border-blue-500/30">
                    <span class="text-blue-400 font-bold text-sm">{{ user.email.charAt(0).toUpperCase() }}</span>
                  </div>
                  <div>
                    <div class="text-white font-medium">{{ user.email }}</div>
                    <div v-if="user.name" class="text-gray-400 text-xs">{{ user.name }}</div>
                  </div>
                </div>
              </td>
              <td class="p-4">
                <span class="px-3 py-1.5 rounded-lg text-xs font-medium inline-flex items-center gap-1.5" :style="roleStyle(user.role)">
                  <span v-if="user.role === 'admin'" class="w-1.5 h-1.5 rounded-full bg-current"></span>
                  {{ user.role }}
                </span>
              </td>
              <td class="p-4">
                <span class="px-3 py-1.5 rounded-lg text-xs font-medium inline-flex items-center gap-1.5" :style="statusStyle(user.status)">
                  <span class="w-1.5 h-1.5 rounded-full bg-current animate-pulse"></span>
                  {{ user.status }}
                </span>
              </td>
              <td class="p-4 text-gray-400 text-sm">{{ formatDate(user.created_at) }}</td>
            <td class="p-4">
              <div class="flex items-center justify-end space-x-2" @click.stop>
                <button
                  v-if="user.status === 'pending_validation'"
                  class="px-3 py-2 rounded-lg text-sm transition-all hover:scale-105"
                  :style="{ backgroundColor: 'var(--accent-green)', color: 'var(--bg)' }"
                  @click="handleApproveUser(user.id)"
                  title="Validate account"
                >
                  Approve
                </button>
                <button
                  v-if="user.status !== 'blocked'"
                  class="px-3 py-2 rounded-lg text-sm transition-all hover:scale-105"
                  :style="{ backgroundColor: 'var(--accent-red)', color: 'var(--bg)' }"
                  @click="handleBlockUser(user.id)"
                  title="Block account"
                >
                  Block
                </button>
                <button 
                  @click.stop="handleDeleteUser(user.id)" 
                  class="p-2 rounded-lg transition-all hover:scale-105" 
                  :style="{ backgroundColor: 'var(--accent-red)', color: 'var(--bg)' }" 
                  title="Delete"
                >
                  <Trash2 class="h-4 w-4" />
                </button>
              </div>
            </td>
          </tr>
            </tbody>
          </table>
        </div>
      </div>

    <!-- User Details Modal -->
    <div v-if="isUserDetailsModalOpen" class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="closeUserDetailsModal">
      <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl border border-gray-700 max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col shadow-2xl">
        <!-- Header -->
        <div class="p-6 border-b border-gray-700 bg-gray-800/50 flex items-center justify-between">
          <div>
            <h2 class="text-2xl font-bold text-white flex items-center gap-2">
              <Activity class="h-6 w-6 text-blue-400" />
              User Details
            </h2>
            <p v-if="selectedUserDetails" class="text-sm text-gray-400 mt-1">{{ selectedUserDetails.user.email }}</p>
          </div>
          <button @click="closeUserDetailsModal" class="p-2 hover:bg-gray-700 rounded-lg transition-colors">
            <X class="h-5 w-5 text-gray-400" />
          </button>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-6">
          <div v-if="userDetailsLoading" class="flex items-center justify-center py-12">
            <div class="text-center">
              <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
              <p class="text-gray-400">Loading user details...</p>
            </div>
          </div>

          <div v-else-if="userDetailsError" class="bg-red-900/20 border border-red-500/50 rounded-lg p-4 text-red-300">
            {{ userDetailsError }}
          </div>

          <div v-else-if="selectedUserDetails" class="space-y-6">
            <!-- User Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <!-- Balance Card -->
              <div class="bg-gradient-to-br from-blue-900/30 to-blue-800/20 rounded-xl p-5 border border-blue-700/30">
                <div class="flex items-center justify-between mb-3">
                  <div class="p-2 bg-blue-500/20 rounded-lg">
                    <Euro class="h-5 w-5 text-blue-400" />
                  </div>
                  <span class="text-xs text-gray-400 font-medium">EUR Balance</span>
                </div>
                <div class="text-2xl font-bold text-white mb-1">{{ formatEUR(selectedUserDetails.balance) }}</div>
                <div class="text-xs text-gray-400">Available funds</div>
              </div>

              <!-- Total Portfolio Value -->
              <div class="bg-gradient-to-br from-green-900/30 to-green-800/20 rounded-xl p-5 border border-green-700/30">
                <div class="flex items-center justify-between mb-3">
                  <div class="p-2 bg-green-500/20 rounded-lg">
                    <Wallet class="h-5 w-5 text-green-400" />
                  </div>
                  <span class="text-xs text-gray-400 font-medium">Portfolio Value</span>
                </div>
                <div class="text-2xl font-bold text-white mb-1">{{ formatEUR(selectedUserDetails.statistics.total_portfolio_value) }}</div>
                <div class="text-xs text-gray-400">Current value</div>
              </div>

              <!-- Gain/Loss -->
              <div :class="[
                'rounded-xl p-5 border',
                selectedUserDetails.statistics.total_gain_loss >= 0
                  ? 'bg-gradient-to-br from-green-900/30 to-green-800/20 border-green-700/30'
                  : 'bg-gradient-to-br from-red-900/30 to-red-800/20 border-red-700/30'
              ]">
                <div class="flex items-center justify-between mb-3">
                  <div :class="['p-2 rounded-lg', selectedUserDetails.statistics.total_gain_loss >= 0 ? 'bg-green-500/20' : 'bg-red-500/20']">
                    <component :is="selectedUserDetails.statistics.total_gain_loss >= 0 ? TrendingUp : TrendingDown" 
                      :class="['h-5 w-5', selectedUserDetails.statistics.total_gain_loss >= 0 ? 'text-green-400' : 'text-red-400']" />
                  </div>
                  <span class="text-xs text-gray-400 font-medium">Gain/Loss</span>
                </div>
                <div :class="['text-2xl font-bold mb-1', selectedUserDetails.statistics.total_gain_loss >= 0 ? 'text-green-400' : 'text-red-400']">
                  {{ selectedUserDetails.statistics.total_gain_loss >= 0 ? '+' : '' }}{{ formatEUR(selectedUserDetails.statistics.total_gain_loss) }}
                </div>
                <div class="text-xs text-gray-400">
                  {{ selectedUserDetails.statistics.total_gain_loss_percent >= 0 ? '+' : '' }}{{ selectedUserDetails.statistics.total_gain_loss_percent.toFixed(2) }}%
                </div>
              </div>
            </div>

            <!-- Statistics Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700">
                <div class="text-xs text-gray-400 mb-1">Total Transactions</div>
                <div class="text-xl font-bold text-white">{{ selectedUserDetails.statistics.total_transactions }}</div>
              </div>
              <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700">
                <div class="text-xs text-gray-400 mb-1">Buy Orders</div>
                <div class="text-xl font-bold text-green-400">{{ selectedUserDetails.statistics.buy_transactions }}</div>
              </div>
              <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700">
                <div class="text-xs text-gray-400 mb-1">Sell Orders</div>
                <div class="text-xl font-bold text-red-400">{{ selectedUserDetails.statistics.sell_transactions }}</div>
              </div>
              <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700">
                <div class="text-xs text-gray-400 mb-1">Total Volume</div>
                <div class="text-xl font-bold text-white">{{ formatEUR(selectedUserDetails.statistics.total_volume) }}</div>
              </div>
            </div>

            <!-- Portfolio -->
            <div v-if="selectedUserDetails.portfolio && selectedUserDetails.portfolio.length > 0">
              <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <Coins class="h-5 w-5 text-blue-400" />
                Portfolio Holdings
              </h3>
              <div class="bg-gray-800/50 rounded-lg border border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                  <table class="w-full">
                    <thead class="bg-gray-800/70 border-b border-gray-700">
                      <tr>
                        <th class="text-left p-3 text-xs text-gray-400 font-medium uppercase">Crypto</th>
                        <th class="text-right p-3 text-xs text-gray-400 font-medium uppercase">Quantity</th>
                        <th class="text-right p-3 text-xs text-gray-400 font-medium uppercase">Current Price</th>
                        <th class="text-right p-3 text-xs text-gray-400 font-medium uppercase">Value</th>
                        <th class="text-right p-3 text-xs text-gray-400 font-medium uppercase">Gain/Loss</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(item, idx) in selectedUserDetails.portfolio" :key="idx" class="border-b border-gray-700/50 hover:bg-gray-700/30">
                        <td class="p-3">
                          <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-gray-700 rounded-lg flex items-center justify-center">
                              <span class="text-white font-bold text-xs">{{ item.crypto?.symbol || 'N/A' }}</span>
                            </div>
                            <div>
                              <div class="text-white font-medium text-sm">{{ item.crypto?.symbol || 'N/A' }}</div>
                              <div class="text-gray-400 text-xs">{{ item.crypto?.name || 'Unknown' }}</div>
                            </div>
                          </div>
                        </td>
                        <td class="p-3 text-right text-white font-medium">{{ parseFloat(item.quantity || 0).toFixed(8) }}</td>
                        <td class="p-3 text-right text-gray-300">{{ formatEUR(item.current_price || 0) }}</td>
                        <td class="p-3 text-right text-white font-medium">{{ formatEUR(item.current_value || 0) }}</td>
                        <td class="p-3 text-right">
                          <div :class="[
                            'font-medium',
                            (item.gain_loss || 0) >= 0 ? 'text-green-400' : 'text-red-400'
                          ]">
                            {{ (item.gain_loss || 0) >= 0 ? '+' : '' }}{{ formatEUR(item.gain_loss || 0) }}
                          </div>
                          <div :class="[
                            'text-xs',
                            (item.gain_loss_percent || 0) >= 0 ? 'text-green-400/70' : 'text-red-400/70'
                          ]">
                            {{ (item.gain_loss_percent || 0) >= 0 ? '+' : '' }}{{ (item.gain_loss_percent || 0).toFixed(2) }}%
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div v-else class="bg-gray-800/50 rounded-lg border border-gray-700 p-8 text-center">
              <BarChart3 class="h-12 w-12 text-gray-600 mx-auto mb-3" />
              <p class="text-gray-400">No portfolio holdings</p>
            </div>

            <!-- Recent Transactions -->
            <div v-if="selectedUserDetails.recent_transactions && selectedUserDetails.recent_transactions.length > 0">
              <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <Activity class="h-5 w-5 text-blue-400" />
                Recent Transactions
              </h3>
              <div class="bg-gray-800/50 rounded-lg border border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                  <table class="w-full">
                    <thead class="bg-gray-800/70 border-b border-gray-700">
                      <tr>
                        <th class="text-left p-3 text-xs text-gray-400 font-medium uppercase">Type</th>
                        <th class="text-left p-3 text-xs text-gray-400 font-medium uppercase">Crypto</th>
                        <th class="text-right p-3 text-xs text-gray-400 font-medium uppercase">Quantity</th>
                        <th class="text-right p-3 text-xs text-gray-400 font-medium uppercase">Price</th>
                        <th class="text-right p-3 text-xs text-gray-400 font-medium uppercase">Total</th>
                        <th class="text-right p-3 text-xs text-gray-400 font-medium uppercase">Date</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(tx, idx) in selectedUserDetails.recent_transactions" :key="idx" class="border-b border-gray-700/50 hover:bg-gray-700/30">
                        <td class="p-3">
                          <span :class="[
                            'px-2 py-1 rounded text-xs font-medium',
                            tx.type === 'buy' ? 'bg-green-900/30 text-green-400' : 'bg-red-900/30 text-red-400'
                          ]">
                            {{ tx.type === 'buy' ? 'Buy' : 'Sell' }}
                          </span>
                        </td>
                        <td class="p-3 text-white font-medium">{{ tx.portfolio?.crypto?.symbol || 'N/A' }}</td>
                        <td class="p-3 text-right text-gray-300">{{ parseFloat(tx.quantity || 0).toFixed(8) }}</td>
                        <td class="p-3 text-right text-gray-300">{{ formatEUR(tx.price || 0) }}</td>
                        <td class="p-3 text-right text-white font-medium">{{ formatEUR(tx.total_price || 0) }}</td>
                        <td class="p-3 text-right text-gray-400 text-xs">{{ formatDate(tx.created_at) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div v-else class="bg-gray-800/50 rounded-lg border border-gray-700 p-8 text-center">
              <Activity class="h-12 w-12 text-gray-600 mx-auto mb-3" />
              <p class="text-gray-400">No transactions yet</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create User Modal -->
    <div v-if="isCreateModalOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-gray-800 rounded-xl border border-gray-700 max-w-md w-full p-6">
        <h2 class="text-xl font-bold text-white mb-4">Create New User</h2>

        <form @submit.prevent="handleCreateUser" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">Email <span style="color:var(--accent-red)">*</span></label>
            <input v-model="createFormData.email" type="email" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">First Name <span style="color:var(--accent-red)">*</span></label>
            <input v-model="createFormData.firstName" type="text" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">Last Name <span style="color:var(--accent-red)">*</span></label>
            <input v-model="createFormData.lastName" type="text" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">Role <span style="color:var(--accent-red)">*</span></label>
            <select v-model="createFormData.role" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2">
              <option value="client">Client</option>
              <option value="admin">Admin</option>
            </select>
          </div>

          <div class="flex items-center justify-end space-x-3 pt-4">
            <button type="button" @click="closeCreateModal" class="px-4 py-2 rounded-lg text-gray-300 hover:bg-gray-700">Cancel</button>
            <button type="submit" :disabled="createLoading" class="px-4 py-2 rounded-lg text-white disabled:opacity-60 disabled:cursor-not-allowed" :style="{ backgroundColor: 'var(--accent-green)' }">
              {{ createLoading ? 'Creating...' : 'Create User' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Plus, Trash2, Copy, Check, X, Wallet, TrendingUp, TrendingDown, Coins, Euro, Activity, BarChart3 } from 'lucide-vue-next';
import { approveUser, blockUser, createAdminUser, deleteUser as deleteUserApi, getAdminUsers, getAdminUserDetails } from '@/services/api';
import { formatEUR } from '@/utils/format';
import type { AuthUser } from '@/types';

interface CreateUserData {
  email: string;
  firstName: string;
  lastName: string;
  role: 'client' | 'admin';
}

const users = ref<AuthUser[]>([]);
const loading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const isCreateModalOpen = ref(false);
const createFormData = ref<CreateUserData>({ email: '', firstName: '', lastName: '', role: 'client' });
const createdTempPassword = ref<string | null>(null);
const copiedPassword = ref(false);
const createLoading = ref(false);

// User details modal
const isUserDetailsModalOpen = ref(false);
const selectedUserDetails = ref<any>(null);
const userDetailsLoading = ref(false);
const userDetailsError = ref('');

onMounted(() => {
  fetchUsers();
});

async function fetchUsers() {
  loading.value = true;
  errorMessage.value = '';
  try {
    users.value = await getAdminUsers();
  } catch (e: any) {
    errorMessage.value = e?.response?.data?.message || 'Impossible de charger les utilisateurs';
  } finally {
    loading.value = false;
  }
}

function generateTempPassword() {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
  let password = '';
  for (let i = 0; i < 12; i++) password += chars.charAt(Math.floor(Math.random() * chars.length));
  return password;
}

async function handleCreateUser() {
  if (!createFormData.value.email || !createFormData.value.firstName || !createFormData.value.lastName) return;

  createLoading.value = true;
  errorMessage.value = '';
  try {
    const payload = {
      name: `${createFormData.value.firstName} ${createFormData.value.lastName}`.trim(),
      email: createFormData.value.email
    };
    const { user, temporary_password } = await createAdminUser(payload);
    users.value = [...users.value, user];
    createdTempPassword.value = temporary_password;
    successMessage.value = 'Utilisateur créé. Un email avec mot de passe temporaire a été envoyé.';
    createFormData.value = { email: '', firstName: '', lastName: '', role: 'client' };
    isCreateModalOpen.value = false;
  } catch (e: any) {
    errorMessage.value = e?.response?.data?.message || 'Création impossible';
  } finally {
    createLoading.value = false;
  }
}

function handleCopyPassword() {
  if (createdTempPassword.value) {
    navigator.clipboard.writeText(createdTempPassword.value);
    copiedPassword.value = true;
    setTimeout(() => (copiedPassword.value = false), 2000);
  }
}

async function handleDeleteUser(id: number) {
  if (!confirm('Are you sure you want to delete this user?')) return;
  await deleteUserApi(id);
  users.value = users.value.filter((u) => u.id !== id);
}

async function handleApproveUser(id: number) {
  errorMessage.value = '';
  try {
    const { user, message } = await approveUser(id);
    users.value = users.value.map((u) => (u.id === id ? user : u));
    successMessage.value = message || 'Utilisateur validé';
  } catch (e: any) {
    errorMessage.value = e?.response?.data?.message || 'Validation impossible';
  }
}

async function handleBlockUser(id: number) {
  errorMessage.value = '';
  try {
    const { user, message } = await blockUser(id);
    users.value = users.value.map((u) => (u.id === id ? user : u));
    successMessage.value = message || 'Utilisateur bloqué';
  } catch (e: any) {
    errorMessage.value = e?.response?.data?.message || 'Blocage impossible';
  }
}

function openCreateModal() {
  isCreateModalOpen.value = true;
  createdTempPassword.value = null;
  copiedPassword.value = false;
}

function closeCreateModal() {
  isCreateModalOpen.value = false;
  createdTempPassword.value = null;
}

const maskedPassword = computed(() => {
  if (!createdTempPassword.value) return '';
  const pw = createdTempPassword.value;
  return pw.split('').map((char, i) => (i < 2 || i >= pw.length - 2 ? char : '•')).join('');
});

function roleStyle(role: 'client' | 'admin') {
  if (role === 'admin') {
    return { backgroundColor: 'var(--blue-opacity-20)', color: 'var(--blue)' };
  }
  return { backgroundColor: 'rgb(55,65,81)', color: 'rgb(209,213,219)' };
}

function statusStyle(status: AuthUser['status']) {
  const base = { color: 'rgb(17,24,39)' };
  const map: Record<AuthUser['status'], Record<string, string>> = {
    pending: { backgroundColor: 'rgb(251,191,36)', color: 'rgb(17,24,39)' },
    pending_validation: { backgroundColor: 'rgb(251,146,60)', color: 'rgb(17,24,39)' },
    active: { backgroundColor: 'rgb(52,211,153)', color: 'rgb(17,24,39)' },
    blocked: { backgroundColor: 'rgb(239,68,68)', color: 'rgb(17,24,39)' }
  };
  return map[status] || base;
}

function formatDate(value?: string) {
  if (!value) return '';
  return value.split('T')[0] ?? value;
}

async function openUserDetails(userId: number) {
  isUserDetailsModalOpen.value = true;
  userDetailsLoading.value = true;
  userDetailsError.value = '';
  try {
    selectedUserDetails.value = await getAdminUserDetails(userId);
  } catch (e: any) {
    userDetailsError.value = e?.response?.data?.message || 'Impossible de charger les détails';
  } finally {
    userDetailsLoading.value = false;
  }
}

function closeUserDetailsModal() {
  isUserDetailsModalOpen.value = false;
  selectedUserDetails.value = null;
  userDetailsError.value = '';
}
</script>

<style scoped>
/* Tailwind handles the look */
</style>