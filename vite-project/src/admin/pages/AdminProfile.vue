<template>
  <div class="space-y-4 sm:space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold">Profil Administrateur</h1>
        <p class="text-gray-400 mt-1 text-sm sm:text-base">Gérer vos informations de profil et de sécurité</p>
      </div>
    </div>

    <!-- Success Messages -->
    <div v-if="nameSuccess" class="border rounded-lg p-4 flex items-center space-x-3" :style="successStyle">
      <Check class="h-5 w-5" :style="{ color: 'var(--accent-green)' }" />
      <span :style="{ color: 'var(--accent-green)' }">Nom mis à jour avec succès</span>
    </div>

    <div v-if="passwordSuccess" class="border rounded-lg p-4 flex items-center space-x-3" :style="successStyle">
      <Check class="h-5 w-5" :style="{ color: 'var(--accent-green)' }" />
      <span :style="{ color: 'var(--accent-green)' }">Mot de passe changé avec succès</span>
    </div>

    <div v-if="passwordError" class="border rounded-lg p-4 flex items-center space-x-3" :style="errorStyle">
      <Lock class="h-5 w-5" :style="{ color: 'var(--accent-red)' }" />
      <span :style="{ color: 'var(--accent-red)' }">{{ passwordError }}</span>
    </div>

    <!-- Tabs -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-1">
      <div class="flex space-x-1">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          :class="['flex items-center space-x-2 px-4 py-2 rounded-lg transition-all duration-200', activeTab === tab.id ? 'bg-gray-700 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-700/50']"
        >
          <component :is="tab.icon" class="h-4 w-4" />
          <span class="font-medium">{{ tab.label }}</span>
        </button>
      </div>
    </div>

    <!-- Profile Tab -->
    <div v-if="activeTab === 'profile'">
      <div class="space-y-6">
        <!-- Profile Header Card -->
        <div class="bg-gray-800 rounded-xl p-6 sm:p-8 border border-gray-700">
          <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
            <div class="relative">
              <div
                class="w-20 h-20 sm:w-24 sm:h-24 rounded-full flex items-center justify-center shadow-lg"
                :style="avatarStyle"
              >
                <Shield class="h-12 w-12 sm:h-14 sm:w-14 text-white" />
              </div>
              <div class="absolute -bottom-1 -right-1 h-6 w-6 rounded-full border-4 border-gray-800" :style="{ backgroundColor: 'var(--accent-green)' }"></div>
            </div>

            <div class="flex-1">
              <h2 class="text-xl sm:text-2xl font-bold text-white mb-2">{{ name }}</h2>

              <div class="flex items-center space-x-2 text-gray-400 mb-1">
                <Mail class="h-4 w-4" />
                <span>{{ email }}</span>
              </div>

              <div class="flex items-center space-x-2 mt-2">
                <div class="px-3 py-1 rounded-full text-xs font-medium" :style="adminBadgeStyle">
                  Administrateur
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Edit Name Section -->
        <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 class="text-lg font-semibold text-white">Informations personnelles</h3>
              <p class="text-sm text-gray-400 mt-1">Mettez à jour vos informations de profil</p>
            </div>

            <button
              v-if="!isEditingName"
              @click="isEditingName = true"
              class="px-4 py-2 rounded-lg text-white transition-colors"
              :style="{ backgroundColor: 'var(--blue)' }"
            >
              Modifier
            </button>
          </div>

          <div v-if="isEditingName">
            <form @submit.prevent="handleSaveName" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">
                  Nom <span style="color: var(--accent-red)">*</span>
                </label>
                <input
                  v-model="name"
                  type="text"
                  required
                  placeholder="Entrez votre nom"
                  class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2"
                  :style="{ '--tw-ring-color': 'var(--blue)' }"
                />
              </div>

              <div class="flex items-center space-x-3 pt-2">
                <button
                  type="submit"
                  class="px-4 py-2 rounded-lg text-white transition-colors flex items-center space-x-2"
                  :style="{ backgroundColor: 'var(--accent-green)' }"
                >
                  <Save class="h-4 w-4" />
                  <span>Enregistrer</span>
                </button>

                <button
                  type="button"
                  @click="cancelEditName"
                  class="px-4 py-2 rounded-lg text-gray-300 hover:bg-gray-700 transition-colors"
                >
                  Annuler
                </button>
              </div>
            </form>
          </div>

          <div v-else class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">Nom</label>
              <p class="text-white text-lg">{{ name }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">Email</label>
              <p class="text-white text-lg">{{ email }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Security Tab -->
    <div v-if="activeTab === 'security'">
      <div class="space-y-6">
        <!-- Change Password Section -->
        <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 class="text-lg font-semibold text-white">Changer le mot de passe</h3>
              <p class="text-sm text-gray-400 mt-1">Mettez à jour votre mot de passe pour sécuriser votre compte</p>
            </div>

            <button
              v-if="!isChangingPassword"
              @click="isChangingPassword = true"
              class="px-4 py-2 rounded-lg text-white transition-colors"
              :style="{ backgroundColor: 'var(--blue)' }"
            >
              Changer
            </button>
          </div>

          <div v-if="isChangingPassword">
            <form @submit.prevent="handleChangePassword" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">
                  Mot de passe actuel <span style="color: var(--accent-red)">*</span>
                </label>
                <div class="relative">
                  <input
                    :type="showCurrentPassword ? 'text' : 'password'"
                    v-model="currentPassword"
                    required
                    placeholder="Entrez votre mot de passe actuel"
                    class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 pr-10 text-white placeholder-gray-400 focus:outline-none focus:ring-2"
                    :style="{ '--tw-ring-color': 'var(--blue)' }"
                  />
                  <button type="button" @click="showCurrentPassword = !showCurrentPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white">
                    <component :is="showCurrentPassword ? EyeOff : Eye" class="h-5 w-5" />
                  </button>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">
                  Nouveau mot de passe <span style="color: var(--accent-red)">*</span>
                </label>
                <div class="relative">
                  <input
                    :type="showNewPassword ? 'text' : 'password'"
                    v-model="newPassword"
                    required
                    minlength="8"
                    placeholder="Entrez votre nouveau mot de passe (min. 8 caractères)"
                    class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 pr-10 text-white placeholder-gray-400 focus:outline-none focus:ring-2"
                    :style="{ '--tw-ring-color': 'var(--blue)' }"
                  />
                  <button type="button" @click="showNewPassword = !showNewPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white">
                    <component :is="showNewPassword ? EyeOff : Eye" class="h-5 w-5" />
                  </button>
                </div>
                <p class="text-xs text-gray-500 mt-1">Le mot de passe doit contenir au moins 8 caractères</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">
                  Confirmer le nouveau mot de passe <span style="color: var(--accent-red)">*</span>
                </label>
                <div class="relative">
                  <input
                    :type="showConfirmPassword ? 'text' : 'password'"
                    v-model="confirmPassword"
                    required
                    minlength="8"
                    placeholder="Confirmez votre nouveau mot de passe"
                    class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 pr-10 text-white placeholder-gray-400 focus:outline-none focus:ring-2"
                    :style="{ '--tw-ring-color': 'var(--blue)' }"
                  />
                  <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white">
                    <component :is="showConfirmPassword ? EyeOff : Eye" class="h-5 w-5" />
                  </button>
                </div>

                <p v-if="confirmPassword && newPassword !== confirmPassword" class="text-xs mt-1" :style="{ color: 'var(--accent-red)' }">Les mots de passe ne correspondent pas</p>
                <p v-if="newPassword && newPassword.length < 8" class="text-xs text-yellow-400 mt-1">Le mot de passe doit contenir au moins 8 caractères</p>
                <p v-if="newPassword && newPassword.length >= 8 && confirmPassword && newPassword === confirmPassword" class="text-xs mt-1" :style="{ color: 'var(--accent-green)' }">Les mots de passe correspondent</p>
              </div>

              <div class="flex items-center space-x-3 pt-2">
                <button type="submit" class="px-4 py-2 rounded-lg text-white transition-colors flex items-center space-x-2" :style="{ backgroundColor: 'var(--accent-green)' }">
                  <Save class="h-4 w-4" />
                  <span>Enregistrer</span>
                </button>

                <button type="button" @click="cancelChangePassword" class="px-4 py-2 rounded-lg text-gray-300 hover:bg-gray-700 transition-colors">
                  Annuler
                </button>
              </div>
            </form>
          </div>

          <div v-else class="bg-gray-700/50 rounded-lg p-4 border border-gray-600">
            <p class="text-gray-400 text-sm">Cliquez sur "Changer" pour mettre à jour votre mot de passe</p>
          </div>
        </div>

        <!-- Security Info -->
        <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
          <h3 class="text-lg font-semibold text-white mb-4">Informations de sécurité</h3>
          <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-gray-700/50 rounded-lg">
              <div>
                <p class="text-white font-medium">Authentification à deux facteurs</p>
                <p class="text-sm text-gray-400">Protégez votre compte avec 2FA</p>
              </div>
              <button class="px-4 py-2 rounded-lg text-white transition-colors text-sm" :style="{ backgroundColor: 'var(--blue)' }">Activer</button>
            </div>

            <div class="flex items-center justify-between p-4 bg-gray-700/50 rounded-lg">
              <div>
                <p class="text-white font-medium">Sessions actives</p>
                <p class="text-sm text-gray-400">Gérez vos sessions connectées</p>
              </div>
              <button class="px-4 py-2 rounded-lg text-gray-300 hover:bg-gray-700 transition-colors text-sm">Voir</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { Shield, User, Lock, Mail, Save, Eye, EyeOff, Check } from 'lucide-vue-next';
import { useAuthStore } from '@/stores/auth';
import api from '@/services/api';

const auth = useAuthStore();

const activeTab = ref<'profile' | 'security'>('profile');
const isEditingName = ref(false);
const isChangingPassword = ref(false);

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

const tabs = [
  { id: 'profile', label: 'Profil', icon: User },
  { id: 'security', label: 'Sécurité', icon: Lock }
];

const successStyle = {
  backgroundColor: 'var(--green-opacity-10)',
  borderColor: 'var(--accent-green)'
};
const errorStyle = {
  backgroundColor: 'var(--red-opacity-20)',
  borderColor: 'var(--accent-red)'
};
const avatarStyle = {
  background: `linear-gradient(to bottom right, var(--blue), var(--blue-dark))`,
  boxShadow: 'var(--blue-ring-shadow-lg)'
};
const adminBadgeStyle = {
  backgroundColor: 'var(--blue-opacity-20)',
  color: 'var(--blue)'
};

const adminUser = computed(() => auth.user);

function hydrate() {
  const u = adminUser.value;
  const displayName =
    (u?.first_name || u?.last_name) ? `${u?.first_name ?? ''} ${u?.last_name ?? ''}`.trim() : u?.name || 'Admin';
  name.value = displayName;
  email.value = adminUser.value?.email || '';
}

async function handleSaveName() {
  if (name.value.trim() === '') return;
  try {
    // Option: reuse profile update if we allow admin profile update
    // For now, just update local auth store
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
    passwordError.value = 'Veuillez entrer votre mot de passe actuel';
    return;
  }
  if (newPassword.value.length < 8) {
    passwordError.value = 'Le mot de passe doit contenir au moins 8 caractères';
    return;
  }
  if (newPassword.value !== confirmPassword.value) {
    passwordError.value = 'Les mots de passe ne correspondent pas';
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
    passwordError.value = e?.response?.data?.message || 'Erreur lors du changement de mot de passe';
  }
}

function cancelChangePassword() {
  isChangingPassword.value = false;
  currentPassword.value = '';
  newPassword.value = '';
  confirmPassword.value = '';
  passwordError.value = '';
}

onMounted(() => {
  if (!auth.user && auth.token) {
    auth.fetchCurrentUser().then(hydrate);
  }
  hydrate();
});
</script>

<style scoped>
/* visual transitions */
.fade-enter-active, .fade-leave-active { transition: opacity .15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>