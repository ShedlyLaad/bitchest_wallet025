<template>
  <div class="min-h-screen bg-gray-900 text-white">
    <div class="max-w-6xl mx-auto p-3 sm:p-6 space-y-4 sm:space-y-8">
      <!-- Header -->
      <div class="bg-gray-800 rounded-xl p-4 sm:p-8 border border-gray-700">
        <div class="flex flex-col sm:flex-row items-start justify-between gap-4 sm:gap-6">
          <div class="flex flex-col sm:flex-row items-start gap-4 sm:gap-6 w-full sm:w-auto">
            <div class="relative">
              <div class="w-16 sm:w-20 h-16 sm:h-20 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-xl sm:text-2xl font-bold">
                {{ currentUser.name.charAt(0) }}
              </div>
              <button class="absolute -bottom-1 -right-1 bg-blue-600 hover:bg-blue-700 rounded-full p-2 transition-colors">
                <CameraIcon class="h-3 w-3" />
              </button>
            </div>

            <div class="space-y-2">
              <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <h1 class="text-xl sm:text-2xl font-bold">{{ currentUser.name }}</h1>
                <div v-if="currentUser.isVerified" class="flex items-center space-x-1 px-2 py-1 rounded-full text-xs sm:text-sm PnL--pos" :style="{ backgroundColor: 'var(--accent-green)', opacity: 0.2 }">
                  <CheckIcon class="h-3 w-3" />
                  <span>Verified</span>
                </div>
              </div>
              <p class="text-gray-400 text-sm sm:text-base">{{ currentUser.email }}</p>
              <p class="text-xs sm:text-sm text-gray-500">Member since {{ memberSince }}</p>
            </div>
          </div>

          <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
            <RouterLink to="/app/portfolio" class="w-full sm:w-auto flex items-center justify-center space-x-2 bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg transition-colors">
              <WalletIcon class="h-4 w-4" />
              <span>View portfolio</span>
            </RouterLink>
            <button
              @click="isEditing ? handleSaveProfile() : (isEditing = true)"
              :disabled="savingProfile"
              class="w-full sm:w-auto flex items-center justify-center space-x-2 bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded-lg transition-colors disabled:opacity-60"
            >
              <Edit3Icon class="h-4 w-4" />
              <span>
                {{ isEditing ? (savingProfile ? 'Saving...' : 'Save Changes') : 'Edit Profile' }}
              </span>
            </button>
          </div>
        </div>

        <div v-if="profileMessage" class="mt-4 p-3 rounded bg-emerald-900/50 border border-emerald-700 text-emerald-200 text-sm">
          {{ profileMessage }}
        </div>
        <div v-if="profileError" class="mt-4 p-3 rounded bg-red-900/50 border border-red-700 text-red-200 text-sm">
          {{ profileError }}
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-gray-300 mb-1">First name</label>
            <input
              v-model="profileForm.first_name"
              :disabled="!isEditing"
              class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white disabled:opacity-60"
            />
          </div>
          <div>
            <label class="block text-sm text-gray-300 mb-1">Last name</label>
            <input
              v-model="profileForm.last_name"
              :disabled="!isEditing"
              class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white disabled:opacity-60"
            />
          </div>
          <div>
            <label class="block text-sm text-gray-300 mb-1">Phone</label>
            <input
              v-model="profileForm.phone"
              :disabled="!isEditing"
              class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white disabled:opacity-60"
              placeholder="+33123456789"
            />
          </div>
          <div>
            <label class="block text-sm text-gray-300 mb-1">Email (read only)</label>
            <input
              :value="currentUser.email"
              disabled
              class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-gray-400"
            />
          </div>
        </div>

        <div class="mt-6 sm:mt-8 grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-6">
          <div class="bg-gray-700/30 rounded-lg p-4">
            <div v-if="isBalanceLoading" class="text-2xl font-bold PnL--pos">
              <div class="w-32 h-8 bg-gray-600/50 rounded animate-pulse"></div>
            </div>
            <div v-else class="text-2xl font-bold PnL--pos">{{ formattedBalance }}</div>
            <div class="text-gray-400 text-sm">Account Balance</div>
          </div>

          <div class="bg-gray-700/30 rounded-lg p-4">
            <div class="text-2xl font-bold text-app-secondary">{{ transactions.length }}</div>
            <div class="text-gray-400 text-sm">Total Transactions</div>
          </div>

          <div class="bg-gray-700/30 rounded-lg p-4">
            <div class="text-2xl font-bold text-purple-400">Level 2</div>
            <div class="text-gray-400 text-sm">Verification Level</div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="bg-gray-800 rounded-xl p-4 sm:p-6 border border-gray-700">
        <div class="flex space-x-4 border-b border-gray-700 pb-4 mb-4">
          <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" :class="['px-3 py-2 rounded-md', activeTab === tab.id ? 'bg-blue-600 text-white' : 'text-gray-300']">
            <span class="inline-flex items-center gap-2"><component :is="tab.icon" class="h-4 w-4" />{{ tab.label }}</span>
          </button>
        </div>

        <div v-if="activeTab === 'overview'">
          <h3 class="text-lg font-semibold mb-3">Profile Overview</h3>
          <p class="text-gray-300 mb-4">Basic account details and recent activity.</p>
          <!-- simple recent transactions -->
          <div class="space-y-3">
            <div v-for="tx in recentTransactions" :key="tx.id" class="bg-gray-700/30 rounded-lg p-3 flex items-center justify-between">
              <div>
                <div class="font-medium">{{ tx.type.toUpperCase() }} {{ tx.cryptocurrency }}</div>
                <div class="text-sm text-gray-400">{{ new Date(tx.timestamp).toLocaleString() }}</div>
              </div>
              <div class="text-right">
                <div class="font-semibold">{{ formatEUR(tx.total) }}</div>
                <div class="text-sm text-gray-400">{{ tx.status }}</div>
              </div>
            </div>
          </div>
        </div>

        <div v-else-if="activeTab === 'security'">
          <h3 class="text-lg font-semibold mb-3">Security Settings</h3>
          <div class="space-y-3">
            <div v-for="(s, i) in securitySettings" :key="i" class="bg-gray-700/30 rounded-lg p-3 flex items-start justify-between">
              <div>
                <div class="font-medium">{{ s.name }}</div>
                <div class="text-sm text-gray-400">{{ s.description }}</div>
              </div>
              <div>
                <span :class="s.enabled ? 'text-green-400' : 'text-gray-400'">{{ s.enabled ? 'Enabled' : 'Disabled' }}</span>
              </div>
            </div>
          </div>
        </div>

        <div v-else-if="activeTab === 'payment'">
          <h3 class="text-lg font-semibold mb-3">Payment Methods</h3>
          <div class="space-y-3">
            <div v-for="pm in paymentMethods" :key="pm.id" class="bg-gray-700/30 rounded-lg p-3 flex items-center justify-between">
              <div>
                <div class="font-medium">{{ pm.name }}</div>
                <div class="text-sm text-gray-400">{{ pm.type }}</div>
              </div>
              <div class="text-sm">
                <span class="mr-3" v-if="pm.verified">Verified</span>
                <button class="px-3 py-1 bg-gray-600 rounded text-sm">Manage</button>
              </div>
            </div>
          </div>
        </div>

        <div v-else-if="activeTab === 'history'">
          <h3 class="text-lg font-semibold mb-3">Transaction History</h3>
          <div class="space-y-2">
            <div v-for="tx in transactions" :key="tx.id" class="bg-gray-700/30 rounded-lg p-3 flex items-center justify-between">
              <div>
                <div class="font-medium">{{ tx.type }} {{ tx.cryptocurrency }}</div>
                <div class="text-sm text-gray-400">{{ new Date(tx.timestamp).toLocaleDateString() }}</div>
              </div>
              <div class="text-right">
                <div class="font-semibold">{{ formatEUR(tx.total) }}</div>
                <div class="text-sm text-gray-400">{{ tx.status }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer - Full Width -->
    <FooterSection />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { RouterLink } from 'vue-router';
import {
  User as UserIcon,
  Shield as ShieldIcon,
  CreditCard as CreditCardIcon,
  History as HistoryIcon,
  Check as CheckIcon,
  Edit3 as Edit3Icon,
  Camera as CameraIcon,
  Wallet as WalletIcon
} from 'lucide-vue-next';

import FooterSection from '../components/sectionsLanding/FooterSection.vue';
import { formatEUR } from '../utils/formatEUR';
import { useAuthStore } from '@/stores/auth';
import api from '@/services/api';

const activeTab = ref('overview');
const isEditing = ref(false);
const savingProfile = ref(false);
const profileMessage = ref('');
const profileError = ref('');
const isBalanceLoading = ref(true);
const auth = useAuthStore();
const profileForm = ref({
  first_name: '',
  last_name: '',
  phone: ''
});

onMounted(async () => {
  if (!auth.user && auth.token) {
    await auth.fetchCurrentUser();
  }
  hydrateProfileForm();
  const timer = setTimeout(() => {
    isBalanceLoading.value = false;
    clearTimeout(timer);
  }, 400);
});

watch(
  () => auth.user,
  () => hydrateProfileForm(),
  { immediate: true }
);

const tabs = [
  { id: 'overview', label: 'Overview', icon: UserIcon },
  { id: 'security', label: 'Security', icon: ShieldIcon },
  { id: 'payment', label: 'Payment Methods', icon: CreditCardIcon },
  { id: 'history', label: 'Transaction History', icon: HistoryIcon }
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

const currentUser = computed(() => auth.user ?? { name: '', email: '', phone: '' });
const userName = computed(() => auth.user?.name ?? 'Utilisateur');
const userEmail = computed(() => auth.user?.email ?? 'email inconnu');
const balanceValue = computed(() => auth.user?.euro_balance ?? 0);
const formattedBalance = computed(() => formatEUR(balanceValue.value));
const memberSince = computed(() => {
  const date = auth.user?.created_at;
  return date ? new Date(date).toLocaleDateString() : '';
});

const transactions = ref<any[]>([]);
const recentTransactions = computed(() => transactions.value.slice(0, 5));

function hydrateProfileForm() {
  const u = auth.user;
  profileForm.value = {
    first_name: u?.first_name || (u?.name?.split(' ')[0] ?? ''),
    last_name: u?.last_name || (u?.name?.split(' ').slice(1).join(' ') ?? ''),
    phone: u?.phone || ''
  };
}

async function handleSaveProfile() {
  if (savingProfile.value) return;
  profileMessage.value = '';
  profileError.value = '';
  savingProfile.value = true;
  try {
    const { user, message } = await api.updateProfile(profileForm.value);
    auth.user = user;
    auth.persist();
    profileMessage.value = message || 'Profil mis à jour';
    isEditing.value = false;
  } catch (e: any) {
    profileError.value = e?.response?.data?.message || 'Mise à jour impossible';
  } finally {
    savingProfile.value = false;
  }
}
</script>

<style scoped>
/* layout & colors managed by Tailwind */
</style>