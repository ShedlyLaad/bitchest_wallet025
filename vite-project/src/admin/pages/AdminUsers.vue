<template>
  <div class="space-y-4 sm:space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold">Users Management</h1>
        <p class="text-gray-400 mt-1 text-sm sm:text-base">Manage platform users</p>
      </div>

      <button
        @click="openCreateModal"
        class="flex items-center space-x-2 px-4 py-2 rounded-lg text-white transition-colors"
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
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-x-auto">
      <div v-if="errorMessage" class="px-4 py-3 text-sm text-red-400 border-b border-gray-700">{{ errorMessage }}</div>
      <div v-else-if="loading" class="px-4 py-3 text-sm text-gray-400 border-b border-gray-700">Loading users...</div>
      <div v-else-if="successMessage" class="px-4 py-3 text-sm text-green-400 border-b border-gray-700">Nouvelle création : email envoyé.</div>
      <table class="w-full">
        <thead>
          <tr class="border-b border-gray-700">
            <th class="text-left p-4 text-gray-400 font-medium">Email</th>
            <th class="text-left p-4 text-gray-400 font-medium">Role</th>
            <th class="text-left p-4 text-gray-400 font-medium">Status</th>
            <th class="text-left p-4 text-gray-400 font-medium">Created At</th>
            <th class="text-right p-4 text-gray-400 font-medium">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id" class="border-b border-gray-700 hover:bg-gray-700/30 transition-colors">
            <td class="p-4 text-white">{{ user.email }}</td>
            <td class="p-4">
              <span class="px-3 py-1 rounded-full text-xs font-medium" :style="roleStyle(user.role)">{{ user.role }}</span>
            </td>
            <td class="p-4">
              <span class="px-3 py-1 rounded-full text-xs font-medium" :style="statusStyle(user.status)">{{ user.status }}</span>
            </td>
            <td class="p-4 text-gray-400">{{ formatDate(user.created_at) }}</td>
            <td class="p-4">
              <div class="flex items-center justify-end space-x-2">
                <button
                  v-if="user.status === 'pending_validation'"
                  class="px-3 py-2 rounded-lg text-sm"
                  :style="{ backgroundColor: 'var(--accent-green)', color: 'var(--bg)' }"
                  @click="handleApproveUser(user.id)"
                  title="Validate account"
                >
                  Approve
                </button>
                <button
                  v-if="user.status !== 'blocked'"
                  class="px-3 py-2 rounded-lg text-sm"
                  :style="{ backgroundColor: 'var(--accent-red)', color: 'var(--bg)' }"
                  @click="handleBlockUser(user.id)"
                  title="Block account"
                >
                  Block
                </button>
                <button @click="handleDeleteUser(user.id)" class="p-2 rounded-lg" :style="{ backgroundColor: 'var(--accent-red)', color: 'var(--bg)' }" title="Delete"><Trash2 class="h-4 w-4" /></button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
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
import { Plus, Trash2, Copy, Check } from 'lucide-vue-next';
import { approveUser, blockUser, createAdminUser, deleteUser as deleteUserApi, getAdminUsers } from '@/services/api';
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
</script>

<style scoped>
/* Tailwind handles the look */
</style>