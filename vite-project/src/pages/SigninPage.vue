<template>
  <div class="min-h-screen bg-gray-900 text-white flex">
    <!-- Left Side - Info -->
    <div class="hidden lg:flex flex-1 bg-gradient-to-br from-blue-900/50 to-purple-900/50 items-center justify-center p-8">
      <div class="max-w-md space-y-8">
        <div class="text-center">
          <img :src="Logo1" alt="E-QANAOUITA Logo" class="h-24 w-auto mx-auto mb-4" />
          <h3 class="text-2xl font-bold mb-4 bg-gradient-to-r from-blue-300 via-blue-400 to-white bg-clip-text text-transparent">
            Welcome Back to E-QANAOUITA
          </h3>
          <p class="text-xl text-gray-300">
            Experience the next generation of digital trading on Morocco's most innovative platform.
          </p>
        </div>

        <div class="grid grid-cols-2 gap-6 text-center">
          <div class="bg-gray-800/50 backdrop-blur-sm rounded-lg p-6 border border-gray-700">
            <div class="text-2xl font-bold text-green-400 mb-2">$50B+</div>
            <div class="text-gray-300 text-sm">Volume Traded</div>
          </div>
          <div class="bg-gray-800/50 backdrop-blur-sm rounded-lg p-6 border border-gray-700">
            <div class="text-2xl font-bold text-blue-400 mb-2">5M+</div>
            <div class="text-gray-300 text-sm">Active Users</div>
          </div>
          <div class="bg-gray-800/50 backdrop-blur-sm rounded-lg p-6 border border-gray-700">
            <div class="text-2xl font-bold text-purple-400 mb-2">200+</div>
            <div class="text-gray-300 text-sm">Countries</div>
          </div>
          <div class="bg-gray-800/50 backdrop-blur-sm rounded-lg p-6 border border-gray-700">
            <div class="text-2xl font-bold text-yellow-400 mb-2">99.9%</div>
            <div class="text-gray-300 text-sm">Uptime</div>
          </div>
        </div>

        <div class="bg-blue-600/10 border border-blue-600/30 rounded-lg p-6">
          <div class="flex items-center space-x-3 mb-3">
            <ShieldIcon class="h-6 w-6 text-blue-400" />
            <span class="font-semibold">Enhanced Security</span>
          </div>
          <p class="text-gray-300 text-sm">
            Your account is protected by advanced security measures including 2FA,
            device verification, and real-time fraud monitoring.
          </p>
        </div>
      </div>
    </div>

    <!-- Right Side - Form -->
    <div class="flex-1 flex items-center justify-center p-8">
      <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
          <RouterLink to="/" class="inline-flex items-center space-x-2 mb-8">
            <img :src="Logo1" alt="E-QANAOUITA Logo" class="h-12 w-auto" />
            <span class="text-2xl font-bold bg-gradient-to-r from-blue-300 via-blue-400 to-white bg-clip-text text-transparent">
              E-QANAOUITA
            </span>
          </RouterLink>

          <h2 class="text-3xl font-bold">Sign In to Your Account</h2>
          <p class="mt-2 text-gray-400">
            Access your crypto portfolio and start trading
          </p>
          <p v-if="infoMessage" class="mt-3 text-sm text-yellow-400">{{ infoMessage }}</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-400 mb-2">
              Email Address
            </label>
            <div class="relative">
              <MailIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
              <input
                id="email"
                name="email"
                type="email"
                required
                v-model="form.email"
                class="w-full bg-gray-800 border border-gray-700 rounded-lg pl-12 pr-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Enter your email address"
              />
            </div>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-400 mb-2">
              Password
            </label>
            <div class="relative">
              <LockIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
              <input
                id="password"
                name="password"
                :type="showPassword ? 'text' : 'password'"
                required
                v-model="form.password"
                class="w-full bg-gray-800 border border-gray-700 rounded-lg pl-12 pr-12 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Enter your password"
              />
              <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white">
                <component :is="showPassword ? EyeOffIcon : EyeIcon" class="h-5 w-5" />
              </button>
            </div>
          </div>

          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
              <input
                id="rememberMe"
                name="rememberMe"
                type="checkbox"
                v-model="form.rememberMe"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 rounded bg-gray-800"
              />
              <label for="rememberMe" class="text-sm text-gray-400">
                Remember me
              </label>
            </div>

            <RouterLink to="/forgot-password" class="text-sm text-blue-400 hover:text-blue-300">
              Forgot password?
            </RouterLink>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-all transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-900 disabled:opacity-60 disabled:cursor-not-allowed"
          >
            {{ loading ? 'Signing in...' : 'Sign In' }}
          </button>
          <p v-if="errorMessage" class="text-sm text-red-400 pt-2">{{ errorMessage }}</p>
        </form>

        <!-- Alternative Login Options -->
        <div class="space-y-4">
          <div class="relative">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-gray-700"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-2 bg-gray-900 text-gray-400">Or continue with</span>
            </div>
          </div>

          <button class="w-full bg-gray-800 hover:bg-gray-700 border border-gray-700 text-white font-medium py-3 px-4 rounded-lg transition-colors flex items-center justify-center space-x-2">
            <SmartphoneIcon class="h-5 w-5" />
            <span>Sign in with SMS</span>
          </button>
        </div>

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
import { Eye, EyeOff, Lock, Mail, Shield, Smartphone } from 'lucide-vue-next';
import { useAuthStore } from '@/stores/auth';
import Logo1 from '@/assets/Logo1.png';

const EyeIcon = Eye;
const EyeOffIcon = EyeOff;
const LockIcon = Lock;
const MailIcon = Mail;
const ShieldIcon = Shield;
const SmartphoneIcon = Smartphone;

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
  if (reason === 'pending_validation') return 'Votre compte est en attente de validation par un administrateur.';
  if (reason === 'blocked') return 'Votre compte est bloqué. Contactez le support.';
  if (reason === 'pending') return 'Veuillez changer votre mot de passe temporaire avant de continuer.';
  return '';
});

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
    errorMessage.value = e?.response?.data?.message || 'Impossible de vous connecter';
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
/* nothing extra — styles by Tailwind utilities */
</style>