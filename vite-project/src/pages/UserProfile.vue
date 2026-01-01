<template>
  <div class="min-h-screen bg-gray-900 text-white">
    <div class="max-w-6xl mx-auto p-3 sm:p-6 space-y-4 sm:space-y-8">
      <!-- Profile Banner -->
      <div class="relative bg-gray-800 rounded-xl overflow-hidden border border-gray-700 h-48 sm:h-64">
        <div 
          v-if="profileBannerUrl" 
          class="absolute inset-0 w-full h-full bg-cover bg-center"
          :style="{ backgroundImage: `url(${profileBannerUrl})` }"
        >
          <div class="absolute inset-0 bg-black/30"></div>
        </div>
        <div v-else class="absolute inset-0 w-full h-full bg-gradient-to-r from-blue-600 to-purple-600"></div>
        
        <!-- Banner Upload Button -->
        <div class="absolute top-4 right-4 flex items-center gap-2 z-20">
          <label 
            class="cursor-pointer bg-black/50 hover:bg-black/70 rounded-full p-2 transition-colors backdrop-blur-sm disabled:opacity-50 disabled:cursor-not-allowed"
            :class="{ 'opacity-50 cursor-not-allowed': uploadingBanner }"
          >
            <CameraIcon v-if="!uploadingBanner" class="h-4 w-4" />
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
            class="bg-red-600/50 hover:bg-red-600/70 rounded-full p-2 transition-colors backdrop-blur-sm"
            :disabled="uploadingBanner"
            title="Supprimer la bannière"
          >
            <XIcon class="h-4 w-4" />
          </button>
        </div>
      </div>

      <!-- Header -->
      <div class="bg-gray-800 rounded-xl p-4 sm:p-8 border border-gray-700 -mt-20 sm:-mt-24 relative z-10">
        <div class="flex flex-col sm:flex-row items-start justify-between gap-4 sm:gap-6">
          <div class="flex flex-col sm:flex-row items-start gap-4 sm:gap-6 w-full sm:w-auto">
            <div class="relative">
              <div 
                v-if="hasProfilePicture && !uploadingPicture"
                class="w-16 sm:w-20 h-16 sm:h-20 rounded-full border-4 border-gray-800 overflow-hidden bg-gray-700"
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
                class="w-16 sm:w-20 h-16 sm:h-20 rounded-full border-4 border-gray-800 overflow-hidden bg-gray-700 flex items-center justify-center"
              >
                <div class="h-6 w-6 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
              </div>
              <div 
                v-else
                class="w-16 sm:w-20 h-16 sm:h-20 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-xl sm:text-2xl font-bold border-4 border-gray-800"
              >
                {{ currentUser.name.charAt(0) }}
              </div>
              <label 
                class="absolute -bottom-1 -right-1 bg-blue-600 hover:bg-blue-700 rounded-full p-2 transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                :class="{ 'opacity-50 cursor-not-allowed': uploadingPicture }"
              >
                <CameraIcon v-if="!uploadingPicture" class="h-3 w-3" />
                <div v-else class="h-3 w-3 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
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
                class="absolute -top-1 -right-1 bg-red-600 hover:bg-red-700 rounded-full p-1 transition-colors z-10"
                :disabled="uploadingPicture"
                title="Supprimer la photo"
              >
                <XIcon class="h-2.5 w-2.5" />
              </button>
            </div>

            <div class="space-y-2">
              <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <h1 class="text-xl sm:text-2xl font-bold">{{ currentUser.name }}</h1>
                <div v-if="auth.user?.status === 'active'" class="flex items-center space-x-1 px-2 py-1 rounded-full text-xs sm:text-sm PnL--pos" :style="{ backgroundColor: 'var(--accent-green)', opacity: 0.2 }">
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

        <!-- Messages Section -->
        <div class="mt-4 space-y-2">
          <div v-if="profileMessage" class="p-3 rounded-lg bg-emerald-900/50 border border-emerald-700 text-emerald-200 text-sm flex items-center justify-between">
            <span>{{ profileMessage }}</span>
            <button @click="profileMessage = ''" class="text-emerald-300 hover:text-emerald-100 ml-2">
              <XIcon class="h-4 w-4" />
            </button>
          </div>
          <div v-if="profileError" class="p-3 rounded-lg bg-red-900/50 border border-red-700 text-red-200 text-sm flex items-center justify-between">
            <span>{{ profileError }}</span>
            <button @click="profileError = ''" class="text-red-300 hover:text-red-100 ml-2">
              <XIcon class="h-4 w-4" />
            </button>
          </div>
          <div v-if="uploadMessage" class="p-3 rounded-lg bg-blue-900/50 border border-blue-700 text-blue-200 text-sm flex items-center justify-between">
            <span>{{ uploadMessage }}</span>
            <button @click="uploadMessage = ''" class="text-blue-300 hover:text-blue-100 ml-2">
              <XIcon class="h-4 w-4" />
            </button>
          </div>
          <div v-if="uploadError" class="p-3 rounded-lg bg-red-900/50 border border-red-700 text-red-200 text-sm flex items-center justify-between">
            <span>{{ uploadError }}</span>
            <button @click="uploadError = ''" class="text-red-300 hover:text-red-100 ml-2">
              <XIcon class="h-4 w-4" />
            </button>
          </div>
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
            <div class="text-2xl font-bold text-app-secondary">0</div>
            <div class="text-gray-400 text-sm">Total Transactions</div>
          </div>

          <div class="bg-gray-700/30 rounded-lg p-4">
            <div class="text-2xl font-bold text-purple-400">Level 2</div>
            <div class="text-gray-400 text-sm">Verification Level</div>
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
  Check as CheckIcon,
  Edit3 as Edit3Icon,
  Camera as CameraIcon,
  Wallet as WalletIcon,
  X as XIcon
} from 'lucide-vue-next';

import FooterSection from '../components/sectionsLanding/FooterSection.vue';
import { formatEUR } from '../utils/formatEUR';
import { useAuthStore } from '@/stores/auth';
import api from '@/services/api';

const isEditing = ref(false);
const savingProfile = ref(false);
const profileMessage = ref('');
const profileError = ref('');
const uploadMessage = ref('');
const uploadError = ref('');
const uploadingPicture = ref(false);
const uploadingBanner = ref(false);
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

const currentUser = computed(() => auth.user ?? { name: '', email: '', phone: '' });
const balanceValue = computed(() => auth.user?.euro_balance ?? 0);
const formattedBalance = computed(() => formatEUR(balanceValue.value));
const memberSince = computed(() => {
  const date = auth.user?.created_at;
  return date ? new Date(date).toLocaleDateString() : '';
});

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
/* layout & colors managed by Tailwind */
</style>