<template>
  <div class="space-y-4 sm:space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-white to-gray-300 bg-clip-text text-transparent">Admin Profile & Security</h1>
        <p class="text-gray-400 mt-1 text-sm sm:text-base">Manage your profile information and security settings</p>
      </div>
    </div>

    <!-- Success Messages -->
    <Transition name="fade">
      <div v-if="nameSuccess" class="bg-green-900/20 border border-green-500/50 rounded-xl p-4 flex items-center space-x-3 shadow-lg">
        <Check class="h-5 w-5 text-green-400 flex-shrink-0" />
        <span class="text-green-400 font-medium">Profile updated successfully</span>
      </div>
    </Transition>

    <Transition name="fade">
      <div v-if="passwordSuccess" class="bg-green-900/20 border border-green-500/50 rounded-xl p-4 flex items-center space-x-3 shadow-lg">
        <Check class="h-5 w-5 text-green-400 flex-shrink-0" />
        <span class="text-green-400 font-medium">Password changed successfully</span>
      </div>
    </Transition>

    <Transition name="fade">
      <div v-if="passwordError" class="bg-red-900/20 border border-red-500/50 rounded-xl p-4 flex items-center space-x-3 shadow-lg">
        <Lock class="h-5 w-5 text-red-400 flex-shrink-0" />
        <span class="text-red-400 font-medium">{{ passwordError }}</span>
      </div>
    </Transition>

    <!-- Tabs -->
    <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl border border-gray-700 p-1 shadow-lg">
      <div class="flex space-x-1">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id as 'profile' | 'security'"
          :class="[
            'flex items-center space-x-2 px-4 py-2.5 rounded-lg transition-all duration-200 font-medium',
            activeTab === tab.id 
              ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-lg shadow-blue-500/20' 
              : 'text-gray-400 hover:text-white hover:bg-gray-700/50'
          ]"
        >
          <component :is="tab.icon" class="h-4 w-4" />
          <span>{{ tab.label }}</span>
        </button>
      </div>
    </div>

    <!-- Profile Tab -->
    <div v-if="activeTab === 'profile'">
      <div class="space-y-6">
        <!-- Profile Header Card -->
        <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl p-6 sm:p-8 border border-gray-700 shadow-xl">
          <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
            <div class="relative">
              <div class="relative w-24 h-24 sm:w-32 sm:h-32 rounded-full overflow-hidden border-4 border-blue-500/30 shadow-2xl">
                <img
                  :src="adminLogoUrl"
                  alt="Admin Profile"
                  class="w-full h-full object-cover"
                  @error="handleImageError"
                  @load="imageLoaded = true"
                />
                <div v-if="!imageLoaded" class="absolute inset-0 bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center">
                  <Shield class="h-12 w-12 sm:h-16 sm:w-16 text-white" />
                </div>
              </div>
              <div class="absolute -bottom-1 -right-1 h-7 w-7 rounded-full border-4 border-gray-900 flex items-center justify-center shadow-lg" :style="{ backgroundColor: 'var(--accent-green)' }">
                <Check class="h-3 w-3 text-white" />
              </div>
            </div>

            <div class="flex-1">
              <h2 class="text-2xl sm:text-3xl font-bold text-white mb-2">{{ name }}</h2>

              <div class="flex items-center space-x-2 text-gray-400 mb-3">
                <Mail class="h-4 w-4" />
                <span class="text-sm sm:text-base">{{ email }}</span>
              </div>

              <div class="flex items-center space-x-3 mt-4">
                <div class="px-4 py-1.5 rounded-full text-xs font-semibold bg-gradient-to-r from-blue-500/20 to-blue-600/20 border border-blue-500/30 text-blue-300">
                  <Shield class="h-3 w-3 inline mr-1.5" />
                  Administrator
                </div>
                <div class="flex items-center space-x-2 text-xs text-gray-400">
                  <div class="h-2 w-2 rounded-full animate-pulse" :style="{ backgroundColor: 'var(--accent-green)' }"></div>
                  <span>Online</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Edit Name Section -->
        <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl p-6 border border-gray-700 shadow-lg">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                <User class="h-5 w-5 text-blue-400" />
                Personal Information
              </h3>
              <p class="text-sm text-gray-400 mt-1">Update your profile information</p>
            </div>

            <button
              v-if="!isEditingName"
              @click="isEditingName = true"
              class="px-4 py-2 rounded-lg text-white transition-all hover:scale-105 shadow-lg shadow-blue-500/20 font-medium"
              :style="{ backgroundColor: 'var(--blue)' }"
            >
              <Edit class="h-4 w-4 inline mr-2" />
              Edit
            </button>
          </div>

          <div v-if="isEditingName">
            <form @submit.prevent="handleSaveName" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">
                  Full Name <span class="text-red-400">*</span>
                </label>
                <input
                  v-model="name"
                  type="text"
                  required
                  placeholder="Enter your full name"
                  class="w-full bg-gray-700/50 border border-gray-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                />
              </div>

              <div class="flex items-center space-x-3 pt-2">
                <button
                  type="submit"
                  class="px-4 py-2 rounded-lg text-white transition-all hover:scale-105 flex items-center space-x-2 shadow-lg shadow-green-500/20 font-medium"
                  :style="{ backgroundColor: 'var(--accent-green)' }"
                >
                  <Save class="h-4 w-4" />
                  <span>Save</span>
                </button>

                <button
                  type="button"
                  @click="cancelEditName"
                  class="px-4 py-2 rounded-lg text-gray-300 hover:bg-gray-700 transition-colors"
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>

          <div v-else class="space-y-4">
            <div class="p-4 bg-gray-700/30 rounded-lg border border-gray-600/50">
              <label class="block text-sm font-medium text-gray-400 mb-1">Full Name</label>
              <p class="text-white text-lg font-medium">{{ name }}</p>
            </div>

            <div class="p-4 bg-gray-700/30 rounded-lg border border-gray-600/50">
              <label class="block text-sm font-medium text-gray-400 mb-1">Email</label>
              <p class="text-white text-lg font-medium">{{ email }}</p>
              <p class="text-xs text-gray-500 mt-1">Email cannot be modified</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Security Tab -->
    <div v-if="activeTab === 'security'">
      <div class="space-y-6">
        <!-- Password Change Section -->
        <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl p-6 border border-gray-700 shadow-lg">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                <Lock class="h-5 w-5 text-blue-400" />
                Change Password
              </h3>
              <p class="text-sm text-gray-400 mt-1">Update your password to secure your account</p>
            </div>

            <button
              v-if="!isChangingPassword"
              @click="isChangingPassword = true"
              class="px-4 py-2 rounded-lg text-white transition-all hover:scale-105 shadow-lg shadow-blue-500/20 font-medium"
              :style="{ backgroundColor: 'var(--blue)' }"
            >
              <Lock class="h-4 w-4 inline mr-2" />
              Change
            </button>
          </div>

          <div v-if="isChangingPassword">
            <form @submit.prevent="handleChangePassword" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">
                  Current Password <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                  <input
                    :type="showCurrentPassword ? 'text' : 'password'"
                    v-model="currentPassword"
                    required
                    placeholder="Enter your current password"
                    class="w-full bg-gray-700/50 border border-gray-600 rounded-lg px-4 py-2.5 pr-10 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                  />
                  <button type="button" @click="showCurrentPassword = !showCurrentPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white transition-colors">
                    <component :is="showCurrentPassword ? EyeOff : Eye" class="h-5 w-5" />
                  </button>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">
                  New Password <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                  <input
                    :type="showNewPassword ? 'text' : 'password'"
                    v-model="newPassword"
                    required
                    minlength="8"
                    placeholder="Enter your new password (min. 8 characters)"
                    class="w-full bg-gray-700/50 border border-gray-600 rounded-lg px-4 py-2.5 pr-10 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                  />
                  <button type="button" @click="showNewPassword = !showNewPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white transition-colors">
                    <component :is="showNewPassword ? EyeOff : Eye" class="h-5 w-5" />
                  </button>
                </div>
                <div class="mt-2 space-y-1">
                  <p class="text-xs text-gray-500">Password requirements:</p>
                  <ul class="text-xs text-gray-500 ml-4 list-disc space-y-0.5">
                    <li :class="newPassword.length >= 8 ? 'text-green-400' : ''">At least 8 characters</li>
                    <li :class="hasUpperCase && hasLowerCase && hasNumber ? 'text-green-400' : ''">Mix of uppercase, lowercase, and numbers</li>
                  </ul>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">
                  Confirm New Password <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                  <input
                    :type="showConfirmPassword ? 'text' : 'password'"
                    v-model="confirmPassword"
                    required
                    minlength="8"
                    placeholder="Confirm your new password"
                    class="w-full bg-gray-700/50 border border-gray-600 rounded-lg px-4 py-2.5 pr-10 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                    :class="confirmPassword && newPassword !== confirmPassword ? 'border-red-500' : confirmPassword && newPassword === confirmPassword ? 'border-green-500' : ''"
                  />
                  <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white transition-colors">
                    <component :is="showConfirmPassword ? EyeOff : Eye" class="h-5 w-5" />
                  </button>
                </div>

                <p v-if="confirmPassword && newPassword !== confirmPassword" class="text-xs mt-2 text-red-400 flex items-center gap-1">
                  <X class="h-3 w-3" />
                  Passwords do not match
                </p>
                <p v-if="newPassword && newPassword.length < 8" class="text-xs mt-2 text-yellow-400 flex items-center gap-1">
                  <AlertCircle class="h-3 w-3" />
                  Password must be at least 8 characters
                </p>
                <p v-if="newPassword && newPassword.length >= 8 && confirmPassword && newPassword === confirmPassword" class="text-xs mt-2 text-green-400 flex items-center gap-1">
                  <Check class="h-3 w-3" />
                  Passwords match
                </p>
              </div>

              <div class="flex items-center space-x-3 pt-2">
                <button type="submit" class="px-4 py-2 rounded-lg text-white transition-all hover:scale-105 flex items-center space-x-2 shadow-lg shadow-green-500/20 font-medium" :style="{ backgroundColor: 'var(--accent-green)' }">
                  <Save class="h-4 w-4" />
                  <span>Save</span>
                </button>

                <button type="button" @click="cancelChangePassword" class="px-4 py-2 rounded-lg text-gray-300 hover:bg-gray-700 transition-colors">
                  Cancel
                </button>
              </div>
            </form>
          </div>

          <div v-else class="bg-gray-700/30 rounded-lg p-4 border border-gray-600/50">
            <p class="text-gray-400 text-sm">Click "Change" to update your password</p>
          </div>
        </div>

        <!-- Security Features Section -->
        <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl p-6 border border-gray-700 shadow-lg">
          <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <Shield class="h-5 w-5 text-blue-400" />
            Security Features
          </h3>
          <div class="space-y-4">
            <!-- Two-Factor Authentication -->
            <div class="flex items-center justify-between p-4 bg-gray-700/30 rounded-lg border border-gray-600/50 hover:bg-gray-700/40 transition-colors">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-500/20 rounded-lg">
                  <Shield class="h-5 w-5 text-blue-400" />
                </div>
                <div>
                  <p class="text-white font-medium">Two-Factor Authentication (2FA)</p>
                  <p class="text-sm text-gray-400">Add an extra layer of security to your account</p>
                </div>
              </div>
              <button class="px-4 py-2 rounded-lg text-white transition-all hover:scale-105 shadow-lg shadow-blue-500/20 text-sm font-medium" :style="{ backgroundColor: 'var(--blue)' }">
                Enable
              </button>
            </div>

            <!-- Active Sessions -->
            <div class="flex items-center justify-between p-4 bg-gray-700/30 rounded-lg border border-gray-600/50 hover:bg-gray-700/40 transition-colors">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-green-500/20 rounded-lg">
                  <Activity class="h-5 w-5 text-green-400" />
                </div>
                <div>
                  <p class="text-white font-medium">Active Sessions</p>
                  <p class="text-sm text-gray-400">View and manage your active sessions</p>
                </div>
              </div>
              <button class="px-4 py-2 rounded-lg text-gray-300 hover:bg-gray-700 transition-colors text-sm font-medium">
                View
              </button>
            </div>

            <!-- Login History -->
            <div class="flex items-center justify-between p-4 bg-gray-700/30 rounded-lg border border-gray-600/50 hover:bg-gray-700/40 transition-colors">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-purple-500/20 rounded-lg">
                  <Clock class="h-5 w-5 text-purple-400" />
                </div>
                <div>
                  <p class="text-white font-medium">Login History</p>
                  <p class="text-sm text-gray-400">Review your account access history</p>
                </div>
              </div>
              <button class="px-4 py-2 rounded-lg text-gray-300 hover:bg-gray-700 transition-colors text-sm font-medium">
                View
              </button>
            </div>

            <!-- Security Alerts -->
            <div class="flex items-center justify-between p-4 bg-gray-700/30 rounded-lg border border-gray-600/50 hover:bg-gray-700/40 transition-colors">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-yellow-500/20 rounded-lg">
                  <Bell class="h-5 w-5 text-yellow-400" />
                </div>
                <div>
                  <p class="text-white font-medium">Security Alerts</p>
                  <p class="text-sm text-gray-400">Configure security notifications</p>
                </div>
              </div>
              <button class="px-4 py-2 rounded-lg text-gray-300 hover:bg-gray-700 transition-colors text-sm font-medium">
                Configure
              </button>
            </div>
          </div>
        </div>

        <!-- Security Recommendations -->
        <div class="bg-gradient-to-br from-blue-900/20 to-blue-800/10 rounded-xl p-6 border border-blue-500/30 shadow-lg">
          <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <AlertCircle class="h-5 w-5 text-blue-400" />
            Security Recommendations
          </h3>
          <div class="space-y-3">
            <div class="flex items-start gap-3 p-3 bg-blue-500/10 rounded-lg border border-blue-500/20">
              <Check class="h-5 w-5 text-blue-400 mt-0.5 flex-shrink-0" />
              <div>
                <p class="text-white font-medium text-sm">Use a strong, unique password</p>
                <p class="text-xs text-gray-400 mt-1">Combine uppercase, lowercase, numbers, and special characters</p>
              </div>
            </div>
            <div class="flex items-start gap-3 p-3 bg-blue-500/10 rounded-lg border border-blue-500/20">
              <Check class="h-5 w-5 text-blue-400 mt-0.5 flex-shrink-0" />
              <div>
                <p class="text-white font-medium text-sm">Enable Two-Factor Authentication</p>
                <p class="text-xs text-gray-400 mt-1">Add an extra layer of security to protect your account</p>
              </div>
            </div>
            <div class="flex items-start gap-3 p-3 bg-blue-500/10 rounded-lg border border-blue-500/20">
              <Check class="h-5 w-5 text-blue-400 mt-0.5 flex-shrink-0" />
              <div>
                <p class="text-white font-medium text-sm">Review active sessions regularly</p>
                <p class="text-xs text-gray-400 mt-1">Monitor and revoke access from unknown devices</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import { Shield, User, Lock, Mail, Save, Eye, EyeOff, Check, Edit, X, AlertCircle, Activity, Clock, Bell } from 'lucide-vue-next';
import { useAuthStore } from '@/stores/auth';
import api from '@/services/api';

const auth = useAuthStore();
const route = useRoute();

const activeTab = ref<'profile' | 'security'>('profile');
const isEditingName = ref(false);
const isChangingPassword = ref(false);
const imageLoaded = ref(false);

// Form states
const name = ref('');
const email = ref('');
const currentPassword = ref('');
const newPassword = ref('');
const confirmPassword = ref('');

// Password visibility
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

// Success / error messages
const nameSuccess = ref(false);
const passwordSuccess = ref(false);
const passwordError = ref('');

// Admin logo URL
const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000';
const adminLogoUrl = computed(() => `${baseUrl}/images/adminLogo.png`);

// Password validation
const hasUpperCase = computed(() => /[A-Z]/.test(newPassword.value));
const hasLowerCase = computed(() => /[a-z]/.test(newPassword.value));
const hasNumber = computed(() => /[0-9]/.test(newPassword.value));

const tabs = [
  { id: 'profile', label: 'Profile', icon: User },
  { id: 'security', label: 'Security', icon: Lock }
];

const adminUser = computed(() => auth.user);

function hydrate() {
  const u = adminUser.value;
  const displayName =
    (u?.first_name || u?.last_name) ? `${u?.first_name ?? ''} ${u?.last_name ?? ''}`.trim() : u?.name || 'Admin';
  name.value = displayName;
  email.value = adminUser.value?.email || '';
}

function handleImageError(event: Event) {
  const target = event.target as HTMLImageElement;
  if (target) {
    imageLoaded.value = false;
    target.style.display = 'none';
  }
}


async function handleSaveName() {
  if (name.value.trim() === '') return;
  try {
    if (auth.user) {
      auth.user.name = name.value.trim();
      auth.persist();
    }
    nameSuccess.value = true;
    isEditingName.value = false;
    setTimeout(() => (nameSuccess.value = false), 3000);
  } catch (_) {
    // ignored
  }
}

function cancelEditName() {
  isEditingName.value = false;
  hydrate();
}

async function handleChangePassword() {
  passwordError.value = '';
  if (!currentPassword.value) {
    passwordError.value = 'Please enter your current password';
    return;
  }
  if (newPassword.value.length < 8) {
    passwordError.value = 'Password must be at least 8 characters';
    return;
  }
  if (!hasUpperCase.value || !hasLowerCase.value || !hasNumber.value) {
    passwordError.value = 'Password must contain uppercase, lowercase, and numbers';
    return;
  }
  if (newPassword.value !== confirmPassword.value) {
    passwordError.value = 'Passwords do not match';
    return;
  }

  try {
    await api.changePassword({
      current_password: currentPassword.value,
      password: newPassword.value,
      password_confirmation: confirmPassword.value
    });
    passwordSuccess.value = true;
    isChangingPassword.value = false;
    currentPassword.value = '';
    newPassword.value = '';
    confirmPassword.value = '';
    setTimeout(() => (passwordSuccess.value = false), 3000);
  } catch (e: any) {
    passwordError.value = e?.response?.data?.message || 'Error changing password';
  }
}

function cancelChangePassword() {
  isChangingPassword.value = false;
  currentPassword.value = '';
  newPassword.value = '';
  confirmPassword.value = '';
  passwordError.value = '';
}

// Watch route query to set active tab
watch(() => route.query.tab, (newTab) => {
  if (newTab === 'security' || newTab === 'profile') {
    activeTab.value = newTab as 'profile' | 'security';
  }
}, { immediate: true });

onMounted(() => {
  // Set active tab from query param
  const tabParam = route.query.tab;
  if (tabParam === 'security' || tabParam === 'profile') {
    activeTab.value = tabParam as 'profile' | 'security';
  }
  
  if (!auth.user && auth.token) {
    auth.fetchCurrentUser().then(hydrate);
  }
  hydrate();
});
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
