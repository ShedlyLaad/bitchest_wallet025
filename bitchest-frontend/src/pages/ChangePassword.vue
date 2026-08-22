<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 px-4 py-8">
    <div class="w-full max-w-md bg-gradient-to-br from-gray-800/95 to-gray-900/95 backdrop-blur-xl border border-gray-700/50 rounded-2xl p-8 sm:p-10 shadow-2xl space-y-6">
      <!-- Header with Icon -->
      <div class="space-y-3 text-center">
        <div class="flex justify-center">
          <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500/20 to-purple-500/20 border-2 border-blue-500/30 flex items-center justify-center">
            <Lock class="h-8 w-8 text-blue-400" />
          </div>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-white">Update Password</h1>
        <p class="text-gray-400 text-sm sm:text-base">
          This account requires a new password before administrator validation.
        </p>
      </div>

      <!-- Success Message -->
      <Transition name="slide-fade">
        <div v-if="successMessage" class="flex items-start gap-3 p-4 rounded-xl border-2 backdrop-blur-sm" style="background-color: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.5);">
          <CheckCircle class="h-5 w-5 flex-shrink-0 mt-0.5" style="color: #10b981;" />
          <div class="flex-1">
            <p class="text-sm font-medium" style="color: #6ee7b7;">{{ successMessage }}</p>
          </div>
        </div>
      </Transition>

      <!-- Error Message -->
      <Transition name="slide-fade">
        <div v-if="errorMessage" class="flex items-start gap-3 p-4 rounded-xl border-2 backdrop-blur-sm" style="background-color: rgba(239, 68, 68, 0.15); border-color: rgba(239, 68, 68, 0.5);">
          <AlertCircle class="h-5 w-5 flex-shrink-0 mt-0.5" style="color: #ef4444;" />
          <div class="flex-1">
            <p class="text-sm font-medium" style="color: #fca5a5;">{{ errorMessage }}</p>
          </div>
        </div>
      </Transition>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="space-y-5">
        <!-- Current Password -->
        <div class="space-y-2">
          <label class="flex items-center gap-2 text-sm font-semibold text-gray-300">
            <Key class="h-4 w-4 text-gray-400" />
            Temporary Password
          </label>
          <div class="relative">
            <input
              v-model="form.current_password"
              :type="showCurrentPassword ? 'text' : 'password'"
              required
              placeholder="Enter your temporary password"
              class="w-full bg-gray-900/50 border-2 border-gray-700 rounded-xl px-4 py-3 pl-11 pr-11 text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30 transition-all duration-200"
            />
            <Lock class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-500" />
            <button
              type="button"
              @click="showCurrentPassword = !showCurrentPassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-gray-500 hover:text-white rounded-lg hover:bg-gray-700/50 transition-all duration-200"
              tabindex="-1"
            >
              <EyeOff v-if="showCurrentPassword" class="h-5 w-5" />
              <Eye v-else class="h-5 w-5" />
            </button>
          </div>
        </div>

        <!-- New Password -->
        <div class="space-y-2">
          <label class="flex items-center gap-2 text-sm font-semibold text-gray-300">
            <Lock class="h-4 w-4 text-gray-400" />
            New Password
            <span class="text-xs font-normal text-gray-500">(min. 8 characters)</span>
          </label>
          <div class="relative">
            <input
              v-model="form.password"
              :type="showNewPassword ? 'text' : 'password'"
              required
              minlength="8"
              placeholder="Enter your new password"
              class="w-full bg-gray-900/50 border-2 rounded-xl px-4 py-3 pl-11 pr-11 text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30 transition-all duration-200"
              :class="{
                'border-gray-700': !form.password,
                'border-red-500/50': form.password && !isPasswordStrongEnough,
                'border-green-500/50': form.password && isPasswordStrongEnough
              }"
            />
            <Lock class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-500" />
            <button
              type="button"
              @click="showNewPassword = !showNewPassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-gray-500 hover:text-white rounded-lg hover:bg-gray-700/50 transition-all duration-200"
              tabindex="-1"
            >
              <EyeOff v-if="showNewPassword" class="h-5 w-5" />
              <Eye v-else class="h-5 w-5" />
            </button>
          </div>

          <!-- Live strength indicator -->
          <div v-if="form.password" class="flex items-center gap-2 text-xs">
            <div class="flex-1 h-1 bg-gray-700 rounded-full overflow-hidden">
              <div
                class="h-full rounded-full transition-all duration-300"
                :class="{
                  'bg-red-500': passwordStrength.level === 1,
                  'bg-yellow-500': passwordStrength.level === 2,
                  'bg-green-500': passwordStrength.level === 3
                }"
                :style="{ width: `${passwordStrength.width}%` }"
              ></div>
            </div>
            <span
              class="font-semibold"
              :class="{
                'text-red-400': passwordStrength.level === 1,
                'text-yellow-400': passwordStrength.level === 2,
                'text-green-400': passwordStrength.level === 3
              }"
            >{{ passwordStrength.label }}</span>
          </div>

          <!-- Live requirements -->
          <ul v-if="form.password" class="space-y-1.5 pt-1">
            <li
              v-for="rule in passwordRules"
              :key="rule.key"
              class="flex items-center gap-2 text-xs"
            >
              <CheckCircle v-if="rule.valid" class="h-3.5 w-3.5 flex-shrink-0 text-green-500" />
              <XCircle v-else class="h-3.5 w-3.5 flex-shrink-0 text-red-500" />
              <span :class="rule.valid ? 'text-green-400' : 'text-gray-400'">{{ rule.label }}</span>
            </li>
          </ul>
        </div>

        <!-- Confirm Password -->
        <div class="space-y-2">
          <label class="flex items-center gap-2 text-sm font-semibold text-gray-300">
            <Lock class="h-4 w-4 text-gray-400" />
            Confirm New Password
          </label>
          <div class="relative">
            <input
              v-model="form.password_confirmation"
              :type="showConfirmPassword ? 'text' : 'password'"
              required
              minlength="8"
              placeholder="Confirm your new password"
              class="w-full bg-gray-900/50 border-2 rounded-xl px-4 py-3 pl-11 pr-16 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 transition-all duration-200"
              :class="{
                'border-gray-700': !form.password_confirmation,
                'border-red-500/50': form.password_confirmation && passwordMismatch,
                'border-green-500/50': form.password_confirmation && !passwordMismatch && isPasswordStrongEnough
              }"
            />
            <Lock class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-500" />
            <CheckCircle
              v-if="form.password_confirmation && !passwordMismatch && isPasswordStrongEnough"
              class="absolute right-10 top-1/2 -translate-y-1/2 h-5 w-5 text-green-500"
            />
            <XCircle
              v-if="form.password_confirmation && passwordMismatch"
              class="absolute right-10 top-1/2 -translate-y-1/2 h-5 w-5 text-red-500"
            />
            <button
              type="button"
              @click="showConfirmPassword = !showConfirmPassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-gray-500 hover:text-white rounded-lg hover:bg-gray-700/50 transition-all duration-200"
              tabindex="-1"
            >
              <EyeOff v-if="showConfirmPassword" class="h-5 w-5" />
              <Eye v-else class="h-5 w-5" />
            </button>
          </div>
          <Transition name="slide-fade">
            <p v-if="form.password_confirmation && passwordMismatch" class="text-xs text-red-400 flex items-center gap-1">
              <AlertCircle class="h-3 w-3" />
              Passwords do not match
            </p>
          </Transition>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="loading || passwordMismatch || !isFormValid"
          class="group relative w-full py-3.5 px-6 rounded-xl text-white font-semibold text-base transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed overflow-hidden"
          :style="{
            background: loading || passwordMismatch || !isFormValid
              ? 'linear-gradient(135deg, #4b5563, #374151)'
              : 'linear-gradient(135deg, #3b82f6, #2563eb)',
            boxShadow: loading || passwordMismatch || !isFormValid
              ? 'none'
              : '0 10px 25px rgba(59, 130, 246, 0.3)'
          }"
        >
          <span class="relative z-10 flex items-center justify-center gap-2">
            <svg v-if="loading" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <Lock v-else class="h-5 w-5" />
            {{ loading ? 'Updating...' : 'Update Password' }}
          </span>
          <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/0 to-transparent group-hover:via-white/10 transition-all duration-700 transform -translate-x-full group-hover:translate-x-full"></div>
        </button>
      </form>

      <!-- Footer Info -->
      <div class="pt-4 border-t border-gray-700/50">
        <p class="text-center text-gray-500 text-xs leading-relaxed">
          <Info class="inline-block h-3 w-3 mr-1" />
          After updating, your account will be pending administrator validation.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { Lock, Key, CheckCircle, AlertCircle, XCircle, Info, Eye, EyeOff } from 'lucide-vue-next';
import { changePassword } from '@/services/api';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const auth = useAuthStore();

const form = reactive({
  current_password: '',
  password: '',
  password_confirmation: ''
});

const loading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

// Check if passwords match
const passwordMismatch = computed(() => {
  if (!form.password || !form.password_confirmation) return false;
  return form.password !== form.password_confirmation;
});

// Mandatory rules, evaluated live (same rules as the backend)
const passwordRules = computed(() => [
  { key: 'length', label: 'At least 8 characters', valid: form.password.length >= 8 },
  { key: 'uppercase', label: 'At least 1 uppercase letter (A-Z)', valid: /[A-Z]/.test(form.password) },
  { key: 'lowercase', label: 'At least 1 lowercase letter (a-z)', valid: /[a-z]/.test(form.password) },
  { key: 'digit', label: 'At least 1 digit (0-9)', valid: /[0-9]/.test(form.password) }
]);

const isPasswordStrongEnough = computed(() => passwordRules.value.every(rule => rule.valid));

// Live strength: Faible / Moyen / Fort
const passwordStrength = computed(() => {
  if (!form.password) return { level: 0, label: '', width: 0 };

  if (!isPasswordStrongEnough.value) return { level: 1, label: 'Faible', width: 33 };

  const isLong = form.password.length >= 12;
  const hasSymbol = /[^A-Za-z0-9]/.test(form.password);

  return isLong || hasSymbol
    ? { level: 3, label: 'Fort', width: 100 }
    : { level: 2, label: 'Moyen', width: 66 };
});

// Check if form is valid
const isFormValid = computed(() => {
  return (
    form.current_password.length > 0 &&
    isPasswordStrongEnough.value &&
    form.password_confirmation.length > 0 &&
    !passwordMismatch.value
  );
});

async function handleSubmit() {
  if (!form.current_password) {
    errorMessage.value = 'Please enter your temporary password.';
    return;
  }

  if (!isPasswordStrongEnough.value) {
    const missing = passwordRules.value.filter(rule => !rule.valid).map(rule => rule.label.toLowerCase());
    errorMessage.value = `Your new password must contain: ${missing.join(', ')}.`;
    return;
  }

  if (passwordMismatch.value || !form.password_confirmation) {
    errorMessage.value = 'The two passwords do not match.';
    return;
  }

  loading.value = true;
  successMessage.value = '';
  errorMessage.value = '';
  
  try {
    const { message } = await changePassword(form);
    successMessage.value = message || 'Password updated successfully.';
    
    // Clear form
    form.current_password = '';
    form.password = '';
    form.password_confirmation = '';
    showCurrentPassword.value = false;
    showNewPassword.value = false;
    showConfirmPassword.value = false;
    
    // Logout and redirect
    await auth.logout();
    setTimeout(() => {
      router.push({ name: 'Signin', query: { reason: 'pending_validation' } });
    }, 1500);
  } catch (e: any) {
    const validationErrors = e?.response?.data?.errors;
    errorMessage.value =
      (validationErrors && Object.values(validationErrors).flat()[0] as string) ||
      e?.response?.data?.message ||
      'Failed to update password. Please try again.';
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.2s ease-in;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(-10px);
  opacity: 0;
}
</style>
