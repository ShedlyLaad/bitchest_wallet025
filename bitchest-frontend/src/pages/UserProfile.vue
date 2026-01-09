<template>
  <div class="min-h-screen bg-gray-900 text-white relative overflow-hidden">
    <!-- Enhanced Animated Background -->
    <div class="absolute inset-0 pointer-events-none z-0">
      <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-gray-900 via-gray-900 to-gray-950"></div>
      <div 
        class="absolute top-1/4 -left-40 w-96 h-96 rounded-full blur-3xl opacity-10 animate-pulse"
        :style="{ backgroundColor: 'var(--blue-dark)' }"
      ></div>
      <div 
        class="absolute bottom-1/4 -right-40 w-96 h-96 rounded-full blur-3xl opacity-10 animate-pulse delay-1000"
        :style="{ backgroundColor: 'var(--purple)' }"
      ></div>
      <div class="absolute inset-0 opacity-[0.02]" style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 50px 50px;"></div>
    </div>

    <div class="max-w-6xl mx-auto p-3 sm:p-6 space-y-4 sm:space-y-8 relative z-10">
      <!-- Enhanced Profile Banner -->
      <div class="relative bg-gradient-to-br from-gray-800 via-gray-800 to-gray-900 rounded-2xl overflow-hidden border border-white/10 h-48 sm:h-64 backdrop-blur-sm shadow-2xl">
        <div 
          v-if="profileBannerUrl" 
          class="absolute inset-0 w-full h-full bg-cover bg-center"
          :style="{ backgroundImage: `url(${profileBannerUrl})` }"
        >
          <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        </div>
        <div v-else class="absolute inset-0 w-full h-full bg-gradient-to-br from-blue-600/20 via-purple-600/20 to-pink-600/20"></div>
        
        <!-- Banner Upload Button -->
        <div class="absolute top-4 right-4 flex items-center gap-2 z-20">
          <label 
            class="cursor-pointer bg-white/10 hover:bg-white/20 rounded-lg p-2 transition-all backdrop-blur-md border border-white/20 disabled:opacity-50 disabled:cursor-not-allowed"
            :class="{ 'opacity-50 cursor-not-allowed': uploadingBanner }"
          >
            <CameraIcon v-if="!uploadingBanner" class="h-4 w-4 text-white" />
            <div v-else class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
            <input 
              type="file" 
              accept="image/*" 
              @change="handleBannerUpload" 
              class="hidden"
              :disabled="uploadingBanner"
            />
          </label>
          <button 
            v-if="hasProfileBanner && !uploadingBanner && isEditing"
            @click.prevent.stop="handleDeleteBanner"
            class="bg-red-500/20 hover:bg-red-500/30 rounded-lg p-2 transition-all backdrop-blur-md border border-red-500/30"
            :disabled="uploadingBanner"
            title="Supprimer la bannière"
          >
            <XIcon class="h-4 w-4 text-red-300" />
          </button>
        </div>
      </div>

      <!-- Enhanced Header -->
      <div class="bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-xl rounded-2xl p-4 sm:p-8 border border-white/10 -mt-20 sm:-mt-24 relative z-10 shadow-2xl">
        <div class="flex flex-col sm:flex-row items-start justify-between gap-4 sm:gap-6">
          <div class="flex flex-col sm:flex-row items-start gap-4 sm:gap-6 w-full sm:w-auto">
            <div class="relative">
              <div 
                v-if="hasProfilePicture && !uploadingPicture"
                class="w-20 sm:w-24 h-20 sm:h-24 rounded-2xl border-2 border-white/20 overflow-hidden bg-white/5 backdrop-blur-sm shadow-xl"
              >
                <img 
                  :src="profilePictureUrl || undefined" 
                  :alt="currentUser.name || 'Profile picture'" 
                  class="w-full h-full object-cover"
                  @error="handleImageError"
                  @load="handleImageLoad"
                />
              </div>
              <div 
                v-else-if="uploadingPicture"
                class="w-20 sm:w-24 h-20 sm:h-24 rounded-2xl border-2 border-white/20 overflow-hidden bg-white/5 backdrop-blur-sm flex items-center justify-center"
              >
                <div class="h-6 w-6 border-2 border-blue-400 border-t-transparent rounded-full animate-spin"></div>
              </div>
              <div 
                v-else
                class="w-20 sm:w-24 h-20 sm:h-24 bg-gradient-to-br from-blue-500/80 to-purple-500/80 rounded-2xl flex items-center justify-center text-2xl sm:text-3xl font-bold border-2 border-white/20 backdrop-blur-sm shadow-xl"
              >
                {{ currentUser.name.charAt(0) }}
              </div>
              <label 
                class="absolute -bottom-2 -right-2 bg-white/10 hover:bg-white/20 backdrop-blur-md rounded-lg p-2 transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed border border-white/20 shadow-lg"
                :class="{ 'opacity-50 cursor-not-allowed': uploadingPicture }"
              >
                <CameraIcon v-if="!uploadingPicture" class="h-3.5 w-3.5 text-white" />
                <div v-else class="h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                <input 
                  type="file" 
                  accept="image/*" 
                  @change="handlePictureUpload" 
                  class="hidden"
                  :disabled="uploadingPicture"
                />
              </label>
              <button 
                v-if="hasProfilePicture && !uploadingPicture && isEditing"
                @click.prevent.stop="handleDeletePicture"
                class="absolute -top-2 -right-2 bg-red-500/20 hover:bg-red-500/30 backdrop-blur-md rounded-lg p-1.5 transition-all z-10 border border-red-500/30 shadow-lg"
                :disabled="uploadingPicture"
                title="Supprimer la photo"
              >
                <XIcon class="h-3 w-3 text-red-300" />
              </button>
            </div>

            <div class="space-y-2">
              <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <h1 class="text-2xl sm:text-3xl font-bold text-white">{{ currentUser.name }}</h1>
                <!-- Verified Badge -->
                <div 
                  v-if="auth.user?.status === 'active'" 
                  class="flex items-center gap-1.5 px-2.5 py-1 bg-green-500/20 border border-green-500/30 rounded-lg backdrop-blur-sm"
                  title="Verified Account"
                >
                  <CheckIcon class="h-3.5 w-3.5 text-green-400" />
                  <span class="text-xs font-medium text-green-400">Verified</span>
                </div>
              </div>
              <p class="text-gray-300 text-sm sm:text-base">{{ currentUser.email }}</p>
              <p class="text-xs sm:text-sm text-gray-400">Member since {{ memberSince }}</p>
            </div>
          </div>

          <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
            <RouterLink 
              to="/app/portfolio" 
              class="group w-full sm:w-auto flex items-center justify-center gap-2 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 px-5 py-2.5 rounded-lg transition-all duration-300 shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30 hover:scale-105 font-medium"
            >
              <WalletIcon class="h-4 w-4 transition-transform group-hover:scale-110" />
              <span>View Portfolio</span>
            </RouterLink>
            <button
              @click="isEditing ? handleSaveProfile() : (isEditing = true)"
              :disabled="savingProfile"
              class="w-full sm:w-auto flex items-center justify-center gap-2 bg-gradient-to-r from-gray-700 to-gray-600 hover:from-gray-600 hover:to-gray-500 px-5 py-2.5 rounded-lg transition-all duration-300 shadow-lg shadow-gray-700/20 hover:shadow-gray-600/30 hover:scale-105 disabled:opacity-60 disabled:hover:scale-100 font-medium"
            >
              <Edit3Icon class="h-4 w-4" />
              <span>
                {{ isEditing ? (savingProfile ? 'Saving...' : 'Save Changes') : 'Edit Profile' }}
              </span>
            </button>
          </div>
        </div>

        <!-- Messages Section -->
        <div class="mt-4 space-y-2">
          <Transition name="fade">
            <div v-if="profileMessage" class="p-3 rounded-xl bg-green-500/20 border border-green-500/30 text-green-300 text-sm flex items-center justify-between backdrop-blur-sm">
              <span>{{ profileMessage }}</span>
              <button @click="profileMessage = ''" class="text-green-400 hover:text-green-200 ml-2 transition-colors">
                <XIcon class="h-4 w-4" />
              </button>
            </div>
          </Transition>
          <Transition name="fade">
            <div v-if="profileError" class="p-3 rounded-xl bg-red-500/20 border border-red-500/30 text-red-300 text-sm flex items-center justify-between backdrop-blur-sm">
              <span>{{ profileError }}</span>
              <button @click="profileError = ''" class="text-red-400 hover:text-red-200 ml-2 transition-colors">
                <XIcon class="h-4 w-4" />
              </button>
            </div>
          </Transition>
          <Transition name="fade">
            <div v-if="uploadMessage" class="p-3 rounded-xl bg-blue-500/20 border border-blue-500/30 text-blue-300 text-sm flex items-center justify-between backdrop-blur-sm">
              <span>{{ uploadMessage }}</span>
              <button @click="uploadMessage = ''" class="text-blue-400 hover:text-blue-200 ml-2 transition-colors">
                <XIcon class="h-4 w-4" />
              </button>
            </div>
          </Transition>
          <Transition name="fade">
            <div v-if="uploadError" class="p-3 rounded-xl bg-red-500/20 border border-red-500/30 text-red-300 text-sm flex items-center justify-between backdrop-blur-sm">
              <span>{{ uploadError }}</span>
              <button @click="uploadError = ''" class="text-red-400 hover:text-red-200 ml-2 transition-colors">
                <XIcon class="h-4 w-4" />
              </button>
            </div>
          </Transition>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-1">
            <label class="block text-xs font-medium text-gray-400 uppercase tracking-wider">First name</label>
            <input
              v-model="profileForm.first_name"
              :disabled="!isEditing"
              class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-500 disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all backdrop-blur-sm"
              placeholder="Enter first name"
            />
          </div>
          <div class="space-y-1">
            <label class="block text-xs font-medium text-gray-400 uppercase tracking-wider">Last name</label>
            <input
              v-model="profileForm.last_name"
              :disabled="!isEditing"
              class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-500 disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all backdrop-blur-sm"
              placeholder="Enter last name"
            />
          </div>
          <div class="space-y-1">
            <label class="block text-xs font-medium text-gray-400 uppercase tracking-wider">Phone</label>
            <input
              v-model="profileForm.phone"
              :disabled="!isEditing"
              class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-500 disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all backdrop-blur-sm"
              placeholder="+33123456789"
            />
          </div>
          <div class="space-y-1">
            <label class="block text-xs font-medium text-gray-400 uppercase tracking-wider">Email</label>
            <input
              :value="currentUser.email"
              disabled
              class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-gray-400 cursor-not-allowed backdrop-blur-sm"
            />
          </div>
        </div>

        <div class="mt-6 sm:mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="group relative bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-sm rounded-xl p-5 border border-white/10 hover:border-green-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-green-500/20 hover:scale-105 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-green-500/0 to-emerald-500/0 group-hover:from-green-500/10 group-hover:to-emerald-500/10 transition-all duration-300"></div>
            <div class="relative flex items-center gap-3 mb-3">
              <div class="p-2 bg-green-500/20 rounded-lg group-hover:bg-green-500/30 transition-colors">
                <WalletIcon class="h-5 w-5 text-green-400" />
              </div>
              <div class="text-xs font-medium text-gray-400 uppercase tracking-wider">Account Balance</div>
            </div>
            <div v-if="isBalanceLoading" class="h-8 w-32 bg-white/10 rounded-lg animate-pulse"></div>
            <div v-else class="text-3xl font-bold text-green-400 group-hover:scale-105 transition-transform">{{ formattedBalance }}</div>
          </div>

          <div class="group relative bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-sm rounded-xl p-5 border border-white/10 hover:border-blue-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-blue-500/20 hover:scale-105 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-500/0 to-cyan-500/0 group-hover:from-blue-500/10 group-hover:to-cyan-500/10 transition-all duration-300"></div>
            <div class="relative flex items-center gap-3 mb-3">
              <div class="p-2 bg-blue-500/20 rounded-lg group-hover:bg-blue-500/30 transition-colors">
                <Activity class="h-5 w-5 text-blue-400" />
              </div>
              <div class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total Transactions</div>
            </div>
            <div v-if="isTransactionsLoading" class="h-8 w-24 bg-white/10 rounded-lg animate-pulse"></div>
            <div v-else class="text-3xl font-bold text-blue-400 group-hover:scale-105 transition-transform">{{ totalTransactions }}</div>
          </div>

          <div class="group relative bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-sm rounded-xl p-5 border border-white/10 hover:border-purple-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-purple-500/20 hover:scale-105 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-purple-500/0 to-pink-500/0 group-hover:from-purple-500/10 group-hover:to-pink-500/10 transition-all duration-300"></div>
            <div class="relative flex items-center gap-3 mb-3">
              <div class="p-2 bg-purple-500/20 rounded-lg group-hover:bg-purple-500/30 transition-colors">
                <CheckIcon class="h-5 w-5 text-purple-400" />
              </div>
              <div class="text-xs font-medium text-gray-400 uppercase tracking-wider">Verification Level</div>
            </div>
            <div v-if="isTransactionsLoading" class="h-8 w-20 bg-white/10 rounded-lg animate-pulse"></div>
            <div v-else class="text-3xl font-bold text-purple-400 group-hover:scale-105 transition-transform">{{ userLevel }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer - Enhanced -->
    <UserFooter />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { RouterLink } from 'vue-router';
import {
  Check as CheckIcon,
  Edit3 as Edit3Icon,
  Camera as CameraIcon,
  Wallet as WalletIcon,
  X as XIcon,
  Activity
} from 'lucide-vue-next';

import UserFooter from '@/components/UserFooter.vue';
import { formatEUR } from '../utils/formatEUR';
import { useAuthStore } from '@/stores/auth';
import api from '@/services/api';
import { getTransactionHistory } from '@/services/api';

const isEditing = ref(false);
const savingProfile = ref(false);
const profileMessage = ref('');
const profileError = ref('');
const uploadMessage = ref('');
const uploadError = ref('');
const uploadingPicture = ref(false);
const uploadingBanner = ref(false);
const isBalanceLoading = ref(true);
const isTransactionsLoading = ref(true);
const totalTransactions = ref(0);
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
  await loadTransactionsCount();
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

const currentUser = computed(() => auth.user ?? { name: '', email: '', phone: '' });
const balanceValue = computed(() => auth.user?.euro_balance ?? 0);
const formattedBalance = computed(() => formatEUR(balanceValue.value));
const memberSince = computed(() => {
  const date = auth.user?.created_at;
  return date ? new Date(date).toLocaleDateString() : '';
});

// Calculate user level based on transactions
const userLevel = computed(() => {
  const count = totalTransactions.value;
  if (count < 5) return 'Level 2';
  if (count < 10) return 'Level 3';
  if (count < 20) return 'Level 4';
  if (count < 30) return 'Level 5';
  if (count < 40) return 'Level 6';
  if (count < 50) return 'Level 7';
  if (count < 60) return 'Level 8';
  if (count < 70) return 'Level 9';
  if (count < 80) return 'Level 10';
  // Continue pattern: every 10 transactions = +1 level after level 5
  return `Level ${Math.min(10 + Math.floor((count - 80) / 10), 20)}`;
});

async function loadTransactionsCount() {
  isTransactionsLoading.value = true;
  try {
    const data = await getTransactionHistory({ per_page: 1, page: 1 });
    totalTransactions.value = data.total || 0;
  } catch (e) {
    console.error('Error loading transactions count:', e);
    totalTransactions.value = 0;
  } finally {
    isTransactionsLoading.value = false;
  }
}

// Helper function to check if a value is a valid image path
function isValidImagePath(value: any): boolean {
  if (!value) return false;
  if (typeof value !== 'string') return false;
  const trimmed = value.trim();
  if (trimmed === '' || trimmed === 'null' || trimmed === 'undefined' || trimmed === 'NULL' || trimmed === 'UNDEFINED') return false;
  return true;
}

// Profile picture and banner URLs
const profilePictureUrl = computed(() => {
  const picture = auth.user?.profile_picture;
  if (isValidImagePath(picture)) {
    const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000';
    // Ensure path uses forward slashes
    const path = (picture as string).replace(/\\/g, '/');
    return `${baseUrl}/storage/${path}`;
  }
  return null;
});

const profileBannerUrl = computed(() => {
  const banner = auth.user?.profile_banner;
  if (isValidImagePath(banner)) {
    const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000';
    // Ensure path uses forward slashes
    const path = (banner as string).replace(/\\/g, '/');
    return `${baseUrl}/storage/${path}`;
  }
  return null;
});

// Strict checks for displaying delete buttons - only show if image URL is valid AND not empty
const hasProfilePicture = computed(() => {
  const picture = auth.user?.profile_picture;
  if (!isValidImagePath(picture)) return false;
  if (!profilePictureUrl.value) return false;
  // Double check that the URL is not just the base URL
  const url = profilePictureUrl.value;
  if (url.includes('/storage/') && url.split('/storage/')[1] && url.split('/storage/')[1].trim() !== '') {
    return true;
  }
  return false;
});

const hasProfileBanner = computed(() => {
  const banner = auth.user?.profile_banner;
  if (!isValidImagePath(banner)) return false;
  if (!profileBannerUrl.value) return false;
  // Double check that the URL is not just the base URL
  const url = profileBannerUrl.value;
  if (url.includes('/storage/') && url.split('/storage/')[1] && url.split('/storage/')[1].trim() !== '') {
    return true;
  }
  return false;
});

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
  
  // Clear previous messages
  profileMessage.value = '';
  profileError.value = '';
  
  // Validate form data before sending
  if (!profileForm.value.first_name || !profileForm.value.first_name.trim()) {
    profileError.value = 'Le prénom est requis';
    setTimeout(() => { profileError.value = ''; }, 5000);
    return;
  }
  
  if (!profileForm.value.last_name || !profileForm.value.last_name.trim()) {
    profileError.value = 'Le nom est requis';
    setTimeout(() => { profileError.value = ''; }, 5000);
    return;
  }
  
  if (!profileForm.value.phone || !profileForm.value.phone.trim()) {
    profileError.value = 'Le numéro de téléphone est requis';
    setTimeout(() => { profileError.value = ''; }, 5000);
    return;
  }
  
  // Validate phone format
  const phoneRegex = /^\+?[0-9\s\-]{6,20}$/;
  if (!phoneRegex.test(profileForm.value.phone.trim())) {
    profileError.value = 'Format de téléphone invalide. Exemple: +33123456789';
    setTimeout(() => { profileError.value = ''; }, 5000);
    return;
  }
  
  savingProfile.value = true;
  
  try {
    const response = await api.updateProfile({
      first_name: profileForm.value.first_name.trim(),
      last_name: profileForm.value.last_name.trim(),
      phone: profileForm.value.phone.trim()
    });
    
    // Check if response is valid
    if (response && response.user) {
      auth.user = response.user;
      if (auth.persist) {
        auth.persist();
      }
      profileMessage.value = response.message || 'Profil mis à jour avec succès';
      profileError.value = ''; // Clear any errors
      isEditing.value = false;
      // Auto-hide success message after 5 seconds
      setTimeout(() => { profileMessage.value = ''; }, 5000);
    } else {
      profileError.value = 'Réponse invalide du serveur';
      setTimeout(() => { profileError.value = ''; }, 7000);
    }
  } catch (e: any) {
    // Check if it's actually an error (status code >= 400)
    const status = e?.response?.status;
    
    // Only show error if it's a real error
    if (status && status >= 400) {
      // Handle validation errors
      if (status === 422 && e?.response?.data?.errors) {
        const errors = e.response.data.errors;
        const errorMessages = Object.values(errors).flat().join(', ');
        profileError.value = errorMessages || 'Erreur de validation';
      } else {
        const errorMessage = e?.response?.data?.message || e?.response?.data?.error || 'Erreur lors de la mise à jour du profil';
        profileError.value = errorMessage;
      }
      // Auto-hide error message after 7 seconds
      setTimeout(() => { profileError.value = ''; }, 7000);
    } else {
      // Check if it's a network error or other issue
      const isNetworkError = !e?.response || e?.code === 'ERR_NETWORK' || e?.message?.includes('Network');
      if (isNetworkError) {
        profileError.value = 'Erreur de connexion. Veuillez vérifier votre connexion internet.';
        setTimeout(() => { profileError.value = ''; }, 7000);
      } else {
        // Other errors - log but don't show generic error
        console.warn('Profile update warning:', e);
        profileError.value = '';
      }
    }
  } finally {
    savingProfile.value = false;
  }
}

async function handlePictureUpload(event: Event) {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  if (!file) return;

  // Clear previous messages immediately
  uploadMessage.value = '';
  uploadError.value = '';

  // Validate file
  if (!file.type.startsWith('image/')) {
    uploadError.value = 'Veuillez sélectionner une image valide';
    setTimeout(() => { uploadError.value = ''; }, 5000);
    target.value = '';
    return;
  }

  if (file.size > 5 * 1024 * 1024) {
    uploadError.value = 'La taille du fichier ne doit pas dépasser 5MB';
    setTimeout(() => { uploadError.value = ''; }, 5000);
    target.value = '';
    return;
  }

  uploadingPicture.value = true;
  uploadError.value = ''; // Ensure error is cleared before upload

  try {
    const response = await api.uploadProfilePicture(file);
    
    // Check if response is valid and has user data
    if (response && response.user) {
      auth.user = response.user;
      if (auth.persist) {
        auth.persist();
      }
      // Clear error immediately on success
      uploadError.value = '';
      uploadMessage.value = response.message || 'Photo de profil téléchargée avec succès';
      // Auto-hide success message after 5 seconds
      setTimeout(() => { uploadMessage.value = ''; }, 5000);
    } else {
      // Invalid response but not necessarily an error
      uploadError.value = 'Réponse invalide du serveur';
      setTimeout(() => { uploadError.value = ''; }, 7000);
    }
    
    target.value = ''; // Reset input
  } catch (e: any) {
    // Check if it's actually an error (status code >= 400)
    const status = e?.response?.status;
    if (status && status >= 400) {
      const errorMessage = e?.response?.data?.message || e?.response?.data?.error || 'Erreur lors du téléchargement de la photo';
      uploadError.value = errorMessage;
      uploadMessage.value = ''; // Clear success message
      // Auto-hide error message after 7 seconds
      setTimeout(() => { uploadError.value = ''; }, 7000);
    } else {
      // If no status code or status < 400, might be a network issue or successful
      console.warn('Upload response issue:', e);
      uploadError.value = '';
    }
  } finally {
    uploadingPicture.value = false;
  }
}

async function handleBannerUpload(event: Event) {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  if (!file) return;

  // Clear previous messages immediately
  uploadMessage.value = '';
  uploadError.value = '';

  // Validate file
  if (!file.type.startsWith('image/')) {
    uploadError.value = 'Veuillez sélectionner une image valide';
    setTimeout(() => { uploadError.value = ''; }, 5000);
    target.value = '';
    return;
  }

  if (file.size > 5 * 1024 * 1024) {
    uploadError.value = 'La taille du fichier ne doit pas dépasser 5MB';
    setTimeout(() => { uploadError.value = ''; }, 5000);
    target.value = '';
    return;
  }

  uploadingBanner.value = true;
  uploadError.value = ''; // Ensure error is cleared before upload

  try {
    const response = await api.uploadProfileBanner(file);
    
    // Check if response is valid and has user data
    if (response && response.user) {
      auth.user = response.user;
      if (auth.persist) {
        auth.persist();
      }
      // Clear error immediately on success
      uploadError.value = '';
      uploadMessage.value = response.message || 'Bannière de profil téléchargée avec succès';
      // Auto-hide success message after 5 seconds
      setTimeout(() => { uploadMessage.value = ''; }, 5000);
    } else {
      // Invalid response but not necessarily an error
      uploadError.value = 'Réponse invalide du serveur';
      setTimeout(() => { uploadError.value = ''; }, 7000);
    }
    
    target.value = ''; // Reset input
  } catch (e: any) {
    // Check if it's actually an error (status code >= 400)
    const status = e?.response?.status;
    if (status && status >= 400) {
      const errorMessage = e?.response?.data?.message || e?.response?.data?.error || 'Erreur lors du téléchargement de la bannière';
      uploadError.value = errorMessage;
      uploadMessage.value = ''; // Clear success message
      // Auto-hide error message after 7 seconds
      setTimeout(() => { uploadError.value = ''; }, 7000);
    } else {
      // If no status code or status < 400, might be a network issue or successful
      console.warn('Upload response issue:', e);
      uploadError.value = '';
    }
  } finally {
    uploadingBanner.value = false;
  }
}

async function handleDeletePicture() {
  if (!confirm('Êtes-vous sûr de vouloir supprimer votre photo de profil ?')) {
    return;
  }

  uploadingPicture.value = true;
  uploadMessage.value = '';
  uploadError.value = '';

  try {
    const { user, message } = await api.deleteProfilePicture();
    auth.user = user;
    if (auth.persist) {
      auth.persist();
    }
    uploadMessage.value = message || 'Photo de profil supprimée avec succès';
    // Auto-hide success message after 5 seconds
    setTimeout(() => { uploadMessage.value = ''; }, 5000);
  } catch (e: any) {
    uploadError.value = e?.response?.data?.message || 'Erreur lors de la suppression de la photo';
    // Auto-hide error message after 7 seconds
    setTimeout(() => { uploadError.value = ''; }, 7000);
  } finally {
    uploadingPicture.value = false;
  }
}

async function handleDeleteBanner() {
  if (!confirm('Êtes-vous sûr de vouloir supprimer votre bannière de profil ?')) {
    return;
  }

  uploadingBanner.value = true;
  uploadMessage.value = '';
  uploadError.value = '';

  try {
    const { user, message } = await api.deleteProfileBanner();
    auth.user = user;
    if (auth.persist) {
      auth.persist();
    }
    uploadMessage.value = message || 'Bannière de profil supprimée avec succès';
    // Auto-hide success message after 5 seconds
    setTimeout(() => { uploadMessage.value = ''; }, 5000);
  } catch (e: any) {
    uploadError.value = e?.response?.data?.message || 'Erreur lors de la suppression de la bannière';
    // Auto-hide error message after 7 seconds
    setTimeout(() => { uploadError.value = ''; }, 7000);
  } finally {
    uploadingBanner.value = false;
  }
}

const imageLoaded = ref(false);

function handleImageError(event: Event) {
  const img = event.target as HTMLImageElement;
  // If image fails to load, hide it and show placeholder
  img.style.display = 'none';
  imageLoaded.value = false;
}

function handleImageLoad() {
  imageLoaded.value = true;
}
</script>

<style scoped>
/* Fade transition for messages */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>