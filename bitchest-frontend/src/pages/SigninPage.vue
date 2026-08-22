<template>
  <div class="min-h-screen bg-gray-900 text-white flex relative overflow-hidden">
    <!-- Enhanced Animated Background -->
    <div class="absolute inset-0 pointer-events-none z-0">
      <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-gray-900 via-gray-900 to-gray-950"></div>
      <div 
        class="absolute top-1/4 -left-40 w-96 h-96 rounded-full blur-3xl opacity-10 animate-pulse"
        :style="{ backgroundColor: 'var(--blue-dark)' }"
      ></div>
      <div 
        class="absolute bottom-1/4 -right-40 w-96 h-96 rounded-full blur-3xl opacity-10 animate-pulse delay-1000"
        :style="{ backgroundColor: 'var(--blue)' }"
      ></div>
      <div class="absolute inset-0 opacity-[0.02]" style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 50px 50px;"></div>
    </div>

    <!-- Left Side - Enhanced Info -->
    <div class="hidden lg:flex flex-1 items-center justify-center p-8 relative z-10">
      <div class="max-w-md space-y-8 w-full">
        <div class="text-center">
          <div class="relative inline-block mb-6">
            <img :src="BitchestLogo" alt="Bitchest Logo" class="h-24 w-auto mx-auto relative z-10 transition-transform duration-500 hover:scale-110" />
            <div class="absolute inset-0 bg-blue-500/20 blur-2xl rounded-full opacity-0 hover:opacity-100 transition-opacity duration-500"></div>
          </div>
          <h3 class="text-2xl font-bold mb-4 bg-gradient-to-r from-blue-300 via-blue-400 to-white bg-clip-text text-transparent">
            Welcome Back
          </h3>
          <p class="text-xl text-gray-300">
            Experience the next generation of digital trading on Tunisia's most innovative platform.
          </p>
        </div>

        <div class="space-y-3">
          <div v-for="(feature, i) in securityFeatures" :key="i" class="group flex items-center space-x-3 p-3 bg-gray-800/30 backdrop-blur-sm rounded-lg border border-gray-700/50 hover:border-blue-500/50 hover:bg-gray-800/50 transition-all duration-300">
            <div class="p-1.5 bg-blue-500/20 rounded-lg group-hover:bg-blue-500/30 transition-colors">
              <CheckCircle class="h-4 w-4 text-blue-400 flex-shrink-0" />
            </div>
            <span class="text-gray-300 group-hover:text-white transition-colors">{{ feature }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Side - Enhanced Form -->
    <div class="flex-1 flex items-center justify-center p-4 sm:p-8 relative z-10">
      <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center space-y-4">
          <RouterLink to="/" class="inline-flex items-center mb-4 group">
            <div class="relative">
              <img :src="BitchestLogo" alt="Bitchest Logo" class="h-12 w-auto transition-transform duration-300 group-hover:scale-110" />
              <div class="absolute inset-0 bg-blue-500/20 blur-xl rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </div>
          </RouterLink>

          <div class="space-y-2">
            <h2 class="text-3xl sm:text-4xl font-bold bg-gradient-to-r from-white via-gray-200 to-gray-300 bg-clip-text text-transparent">
              Sign In to Your Account
            </h2>
            <p class="text-gray-400 text-sm sm:text-base">
              Access your crypto portfolio and start trading
            </p>
          </div>

          <!-- Info Message -->
          <Transition name="slide-fade">
            <div v-if="infoMessage" class="flex items-center justify-center gap-2 p-3 rounded-xl bg-yellow-600/20 border border-yellow-500/30 backdrop-blur-sm">
              <AlertCircle class="h-4 w-4 text-yellow-400 flex-shrink-0" />
              <p class="text-sm text-yellow-400">{{ infoMessage }}</p>
            </div>
          </Transition>
        </div>

        <!-- Enhanced Form -->
        <form @submit.prevent="handleSubmit" class="space-y-5">
          <div class="space-y-2">
            <label for="email" class="flex items-center gap-2 text-sm font-semibold text-gray-300">
              <MailIcon class="h-4 w-4 text-gray-400" />
              Email Address
            </label>
            <div class="relative group">
              <div class="absolute inset-0 bg-gradient-to-r from-blue-500/0 to-purple-500/0 group-hover:from-blue-500/5 group-hover:to-purple-500/5 rounded-xl transition-all duration-300"></div>
              <input
                id="email"
                name="email"
                type="email"
                required
                v-model="form.email"
                class="relative w-full bg-gray-800/50 backdrop-blur-sm border-2 border-gray-700 rounded-xl pl-12 pr-4 py-3.5 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200"
                placeholder="Enter your email address"
              />
              <MailIcon class="absolute left-4 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-500 group-focus-within:text-blue-400 transition-colors" />
            </div>
          </div>

          <div class="space-y-2">
            <label for="password" class="flex items-center gap-2 text-sm font-semibold text-gray-300">
              <LockIcon class="h-4 w-4 text-gray-400" />
              Password
            </label>
            <div class="relative group">
              <div class="absolute inset-0 bg-gradient-to-r from-blue-500/0 to-purple-500/0 group-hover:from-blue-500/5 group-hover:to-purple-500/5 rounded-xl transition-all duration-300"></div>
              <input
                id="password"
                name="password"
                :type="showPassword ? 'text' : 'password'"
                required
                v-model="form.password"
                class="relative w-full bg-gray-800/50 backdrop-blur-sm border-2 border-gray-700 rounded-xl pl-12 pr-12 py-3.5 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200"
                placeholder="Enter your password"
              />
              <LockIcon class="absolute left-4 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-500 group-focus-within:text-blue-400 transition-colors" />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-4 top-1/2 transform -translate-y-1/2 p-1 text-gray-500 hover:text-white rounded-lg hover:bg-gray-700/50 transition-all duration-200"
              >
                <EyeOffIcon v-if="showPassword" class="h-5 w-5" />
                <EyeIcon v-else class="h-5 w-5" />
              </button>
            </div>
          </div>

          <div class="flex items-center justify-between pt-2">
            <label class="flex items-center space-x-2 group cursor-pointer">
              <input
                id="rememberMe"
                name="rememberMe"
                type="checkbox"
                v-model="form.rememberMe"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 rounded bg-gray-800/50 cursor-pointer transition-all"
              />
              <span class="text-sm text-gray-400 group-hover:text-gray-300 transition-colors">Remember me</span>
            </label>

            <RouterLink to="/forgot-password" class="text-sm text-blue-400 hover:text-blue-300 font-medium transition-colors flex items-center gap-1">
              <LockIcon class="h-3.5 w-3.5" />
              Forgot password?
            </RouterLink>
          </div>

          <!-- Error Message -->
          <Transition name="slide-fade">
            <div v-if="errorMessage" class="flex items-start gap-3 p-4 rounded-xl border-2 backdrop-blur-sm" style="background-color: rgba(239, 68, 68, 0.15); border-color: rgba(239, 68, 68, 0.5);">
              <AlertCircle class="h-5 w-5 flex-shrink-0 mt-0.5" style="color: #ef4444;" />
              <p class="text-sm font-medium flex-1" style="color: #fca5a5;">{{ errorMessage }}</p>
            </div>
          </Transition>

          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white font-semibold py-3.5 px-4 rounded-xl transition-all duration-200 transform hover:scale-[1.02] hover:shadow-xl hover:shadow-blue-500/30 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-900 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:scale-100 overflow-hidden"
          >
            <span class="relative z-10 flex items-center justify-center gap-2">
              <svg v-if="loading" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <LockIcon v-else class="h-5 w-5 transition-transform group-hover:scale-110" />
              {{ loading ? 'Signing in...' : 'Sign In' }}
            </span>
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/0 to-transparent group-hover:via-white/10 transition-all duration-700 transform -translate-x-full group-hover:translate-x-full"></div>
          </button>
        </form>

        <!-- Security Notice -->
        <div class="bg-yellow-600/10 border border-yellow-600/30 rounded-lg p-4">
          <div class="flex items-start space-x-3">
            <ShieldIcon class="h-5 w-5 text-yellow-400 mt-0.5 flex-shrink-0" />
            <div>
              <div class="font-medium text-yellow-400 text-sm">Security Notice</div>
              <div class="text-gray-300 text-sm mt-1">
                For your security, we may require additional verification steps.
                Never share your login credentials with anyone.
              </div>
            </div>
          </div>
        </div>

        <!-- Sign Up Link -->
        <div class="text-center">
          <p class="text-gray-400">
            Don't have an account?
            <RouterLink to="/signup" class="text-blue-400 hover:text-blue-300 font-medium"> Create one now</RouterLink>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, computed } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { Eye, EyeOff, Lock, Mail, Shield, AlertCircle, CheckCircle } from 'lucide-vue-next';
import { useAuthStore } from '@/stores/auth';
import BitchestLogo from '@/assets/bitchest_logo.png';

const EyeIcon = Eye;
const EyeOffIcon = EyeOff;
const LockIcon = Lock;
const MailIcon = Mail;
const ShieldIcon = Shield;

const showPassword = ref(false);
const loading = ref(false);
const errorMessage = ref('');
const auth = useAuthStore();
const router = useRouter();
const route = useRoute();
const form = reactive({
  email: '',
  password: '',
  rememberMe: false
});

const infoMessage = computed(() => {
  const reason = route.query.reason as string | undefined;
  if (reason === 'pending_validation') return 'Your account is pending validation by an administrator.';
  if (reason === 'blocked') return 'Your account has been blocked. Please contact support.';
  if (reason === 'pending') return 'Please change your temporary password before continuing.';
  return '';
});

const securityFeatures = [
  'Bank-level 256-bit SSL encryption',
  'Cold storage for the majority of funds',
  'Role-based admin access control',
  'Account validation before first trade'
];

async function handleSubmit() {
  errorMessage.value = '';
  loading.value = true;
  try {
    const data = await auth.login({ email: form.email, password: form.password });
    if (data.must_change_password || data.user?.must_change_password) {
      router.push({ name: 'ChangePassword' });
      return;
    }
    const redirect = (route.query.redirect as string) || (auth.user?.role === 'admin' ? '/admin' : '/dashboard');
    router.push(redirect);
  } catch (e: any) {
    errorMessage.value = e?.response?.data?.message || 'Unable to sign in';
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

.delay-1000 {
  animation-delay: 1s;
}
</style>