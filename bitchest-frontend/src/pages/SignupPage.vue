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

    <!-- Left Side - Enhanced Form -->
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
              Create Your Account
            </h2>
            <p class="mt-2 text-gray-400 text-sm sm:text-base">
              A temporary password (8 digits) will be sent by email. You must change it before admin validation.
            </p>
          </div>
        </div>

        <!-- Enhanced Form -->
        <form @submit.prevent="handleSubmit" class="space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <label class="flex items-center gap-2 text-sm font-semibold text-gray-300">
                <UserIcon class="h-4 w-4 text-gray-400" />
                First Name
              </label>
              <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/0 to-purple-500/0 group-hover:from-blue-500/5 group-hover:to-purple-500/5 rounded-xl transition-all duration-300"></div>
                <input 
                  type="text" 
                  required 
                  v-model="form.first_name"
                  class="relative w-full bg-gray-800/50 backdrop-blur-sm border-2 border-gray-700 rounded-xl pl-12 pr-4 py-3.5 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200"
                  placeholder="Enter your first name" 
                />
                <UserIcon class="absolute left-4 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-500 group-focus-within:text-blue-400 transition-colors" />
              </div>
            </div>
            <div class="space-y-2">
              <label class="flex items-center gap-2 text-sm font-semibold text-gray-300">
                <UserIcon class="h-4 w-4 text-gray-400" />
                Last Name
              </label>
              <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/0 to-purple-500/0 group-hover:from-blue-500/5 group-hover:to-purple-500/5 rounded-xl transition-all duration-300"></div>
                <input 
                  type="text" 
                  required 
                  v-model="form.last_name"
                  class="relative w-full bg-gray-800/50 backdrop-blur-sm border-2 border-gray-700 rounded-xl pl-12 pr-4 py-3.5 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200"
                  placeholder="Enter your last name" 
                />
                <UserIcon class="absolute left-4 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-500 group-focus-within:text-blue-400 transition-colors" />
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <label class="flex items-center gap-2 text-sm font-semibold text-gray-300">
                <MailIcon class="h-4 w-4 text-gray-400" />
                Email Address
              </label>
              <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/0 to-purple-500/0 group-hover:from-blue-500/5 group-hover:to-purple-500/5 rounded-xl transition-all duration-300"></div>
                <input 
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
              <label class="flex items-center gap-2 text-sm font-semibold text-gray-300">
                <MailIcon class="h-4 w-4 text-gray-400" />
                Confirm Email
              </label>
              <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/0 to-purple-500/0 group-hover:from-blue-500/5 group-hover:to-purple-500/5 rounded-xl transition-all duration-300"></div>
                <input 
                  type="email" 
                  required 
                  v-model="form.email_confirmation"
                  class="relative w-full bg-gray-800/50 backdrop-blur-sm border-2 rounded-xl pl-12 pr-4 py-3.5 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all duration-200"
                  :class="form.email_confirmation && form.email !== form.email_confirmation ? 'border-red-500/50' : form.email_confirmation && form.email === form.email_confirmation ? 'border-green-500/50' : 'border-gray-700'"
                  placeholder="Confirm your email address" 
                />
                <MailIcon class="absolute left-4 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-500 group-focus-within:text-blue-400 transition-colors" />
                <CheckCircle
                  v-if="form.email_confirmation && form.email === form.email_confirmation"
                  class="absolute right-4 top-1/2 transform -translate-y-1/2 h-5 w-5 text-green-500"
                />
              </div>
            </div>
          </div>

          <!-- No password input: generated automatically and sent by email -->

          <div class="space-y-4">
            <div class="flex items-start space-x-3">
              <input id="agreeToTerms" name="agreeToTerms" type="checkbox" v-model="form.agreeToTerms" class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 rounded bg-gray-800" required />
              <label for="agreeToTerms" class="text-sm text-gray-400">
                I agree to the
                <RouterLink to="/terms" class="text-blue-400 hover:text-blue-300"> Terms of Service</RouterLink>
                and
                <RouterLink to="/privacy" class="text-blue-400 hover:text-blue-300"> Privacy Policy</RouterLink>
              </label>
            </div>

            <div class="flex items-start space-x-3">
              <input id="subscribeNewsletter" name="subscribeNewsletter" type="checkbox" v-model="form.subscribeNewsletter" class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 rounded bg-gray-800" />
              <label for="subscribeNewsletter" class="text-sm text-gray-400">Subscribe to our newsletter for market updates and trading tips</label>
            </div>
          </div>

          <!-- Success Message -->
          <Transition name="slide-fade">
            <div v-if="successMessage" class="flex items-start gap-3 p-4 rounded-xl border-2 backdrop-blur-sm" style="background-color: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.5);">
              <CheckCircle class="h-5 w-5 flex-shrink-0 mt-0.5" style="color: #10b981;" />
              <p class="text-sm font-medium flex-1" style="color: #6ee7b7;">{{ successMessage }}</p>
            </div>
          </Transition>

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
              <UserIcon v-else class="h-5 w-5 transition-transform group-hover:scale-110" />
              {{ loading ? 'Creating...' : 'Create Account' }}
            </span>
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/0 to-transparent group-hover:via-white/10 transition-all duration-700 transform -translate-x-full group-hover:translate-x-full"></div>
          </button>
        </form>

        <!-- Sign In Link -->
        <div class="text-center">
          <p class="text-gray-400">
            Already have an account?
            <RouterLink to="/signin" class="text-blue-400 hover:text-blue-300 font-medium"> Sign in here</RouterLink>
          </p>
        </div>
      </div>
    </div>

    <!-- Right Side - Enhanced Security Info -->
    <div class="hidden lg:flex flex-1 items-center justify-center p-8 relative z-10">
      <div class="max-w-md space-y-8 w-full">
        <div class="text-center">
          <div class="relative inline-block mb-6">
            <div class="p-6 bg-gradient-to-br from-blue-600/20 to-blue-800/10 rounded-2xl border border-blue-500/30 backdrop-blur-sm hover:border-blue-500/50 transition-all duration-300 hover:shadow-xl hover:shadow-blue-500/20 inline-block">
              <ShieldIcon class="h-16 w-16 text-blue-400 transition-transform duration-300 hover:scale-110" />
            </div>
          </div>
          <h3 class="text-2xl font-bold mb-2 bg-gradient-to-r from-blue-300 via-blue-400 to-white bg-clip-text text-transparent">
            Your Security is Our Priority
          </h3>
          <p class="text-gray-300 leading-relaxed">
            We use industry-leading security measures to protect your funds and personal information.
          </p>
        </div>

        <div class="space-y-3">
          <div v-for="(feature, i) in securityFeatures" :key="i" class="group flex items-center space-x-3 p-3 bg-gray-800/30 backdrop-blur-sm rounded-lg border border-gray-700/50 hover:border-green-500/50 hover:bg-gray-800/50 transition-all duration-300">
            <div class="p-1.5 bg-green-500/20 rounded-lg group-hover:bg-green-500/30 transition-colors">
              <CheckCircleIcon class="h-4 w-4 text-green-400 flex-shrink-0" />
            </div>
            <span class="text-gray-300 group-hover:text-white transition-colors">{{ feature }}</span>
          </div>
        </div>

        <div class="group relative bg-gradient-to-br from-gray-800/60 to-gray-800/40 backdrop-blur-xl rounded-xl p-6 border border-gray-700/50 hover:border-blue-500/50 transition-all duration-300 hover:shadow-xl hover:shadow-blue-500/20 overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-br from-blue-500/0 to-blue-500/0 group-hover:from-blue-500/10 group-hover:to-transparent transition-all duration-300"></div>
          <div class="text-center relative">
            <div class="text-3xl font-bold text-blue-400 mb-2 transition-transform duration-300 group-hover:scale-110">5M+</div>
            <div class="text-gray-300 mb-4">Trusted Users Worldwide</div>
            <div class="flex items-center justify-center space-x-4 text-sm text-gray-400">
              <div class="flex items-center space-x-2 px-3 py-1.5 bg-green-500/10 rounded-lg border border-green-500/20">
                <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                <span>99.9% Uptime</span>
              </div>
              <div class="flex items-center space-x-2 px-3 py-1.5 bg-blue-500/10 rounded-lg border border-blue-500/20">
                <div class="w-2 h-2 bg-blue-400 rounded-full animate-pulse"></div>
                <span>24/7 Support</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { Mail, Shield, User, CheckCircle, AlertCircle } from 'lucide-vue-next';
import BitchestLogo from '@/assets/bitchest_logo.png';
import { register as registerApi } from '@/services/api';

const MailIcon = Mail;
const ShieldIcon = Shield;
const UserIcon = User;
const CheckCircleIcon = CheckCircle;

const loading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
const router = useRouter();

const form = reactive({
  first_name: '',
  last_name: '',
  email: '',
  email_confirmation: '',
  agreeToTerms: false,
  subscribeNewsletter: false
});

const securityFeatures = [
  'Bank-level 256-bit SSL encryption',
  'Cold storage for 95% of funds',
  'Multi-signature wallet security',
  'Regular third-party security audits'
];

async function handleSubmit() {
  successMessage.value = '';
  errorMessage.value = '';
  if (!form.agreeToTerms) {
    errorMessage.value = 'Vous devez accepter les conditions';
    return;
  }
  loading.value = true;
  try {
    const { message } = await registerApi({
      first_name: form.first_name,
      last_name: form.last_name,
      email: form.email,
      email_confirmation: form.email_confirmation
    });
    successMessage.value = message || 'Compte créé. En attente de validation par un administrateur.';
    setTimeout(() => router.push({ name: 'Signin', query: { reason: 'pending' } }), 1200);
  } catch (e: any) {
    errorMessage.value = e?.response?.data?.message || 'Inscription impossible';
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