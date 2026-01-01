<template>
  <div class="min-h-screen bg-gray-900 text-white flex">
    <!-- Left Side - Form -->
    <div class="flex-1 flex items-center justify-center p-8">
      <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
          <RouterLink to="/" class="inline-flex items-center mb-8">
            <img :src="BitchestLogo" alt="Bitchest Logo" class="h-12 w-auto" />
          </RouterLink>

          <h2 class="text-3xl font-bold">Create Your Account</h2>
          <p class="mt-2 text-gray-400">
            Un mot de passe temporaire (8 chiffres) sera envoyé par email. Vous devrez le changer avant validation admin.
          </p>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-2">First Name</label>
              <div class="relative">
                <UserIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
                <input type="text" required v-model="form.first_name"
                  class="w-full bg-gray-800 border border-gray-700 rounded-lg pl-12 pr-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Enter your first name" />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-2">Last Name</label>
              <div class="relative">
                <UserIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
                <input type="text" required v-model="form.last_name"
                  class="w-full bg-gray-800 border border-gray-700 rounded-lg pl-12 pr-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Enter your last name" />
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-2">Email Address</label>
              <div class="relative">
                <MailIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
                <input type="email" required v-model="form.email"
                  class="w-full bg-gray-800 border border-gray-700 rounded-lg pl-12 pr-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Enter your email address" />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-2">Confirm Email Address</label>
              <div class="relative">
                <MailIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
                <input type="email" required v-model="form.email_confirmation"
                  class="w-full bg-gray-800 border border-gray-700 rounded-lg pl-12 pr-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Confirm your email address" />
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

          <button type="submit" :disabled="loading" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-all transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-900 disabled:opacity-60 disabled:cursor-not-allowed">
            {{ loading ? 'Creating...' : 'Create Account' }}
          </button>
          <p v-if="successMessage" class="text-sm text-green-400">{{ successMessage }}</p>
          <p v-if="errorMessage" class="text-sm text-red-400">{{ errorMessage }}</p>
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

    <!-- Right Side - Security Info -->
    <div class="hidden lg:flex flex-1 bg-gradient-to-br from-blue-900/50 to-purple-900/50 items-center justify-center p-8">
      <div class="max-w-md space-y-8">
        <div class="text-center">
          <ShieldIcon class="h-16 w-16 text-blue-400 mx-auto mb-4" />
          <h3 class="text-2xl font-bold mb-2">Your Security is Our Priority</h3>
          <p class="text-gray-300">
            We use industry-leading security measures to protect your funds and personal information.
          </p>
        </div>

        <div class="space-y-4">
          <div v-for="(feature, i) in securityFeatures" :key="i" class="flex items-center space-x-3">
            <CheckCircleIcon class="h-5 w-5 text-green-400 flex-shrink-0" />
            <span class="text-gray-300">{{ feature }}</span>
          </div>
        </div>

        <div class="bg-gray-800/50 backdrop-blur-sm rounded-lg p-6 border border-gray-700">
          <div class="text-center">
            <div class="text-3xl font-bold text-blue-400 mb-2">5M+</div>
            <div class="text-gray-300 mb-4">Trusted Users Worldwide</div>
            <div class="flex items-center justify-center space-x-4 text-sm text-gray-400">
              <div class="flex items-center space-x-1">
                <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                <span>99.9% Uptime</span>
              </div>
              <div class="flex items-center space-x-1">
                <div class="w-2 h-2 bg-blue-400 rounded-full"></div>
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
import { Mail, Shield, User, CheckCircle } from 'lucide-vue-next';
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
/* Tailwind used, no extra rules required here */
</style>