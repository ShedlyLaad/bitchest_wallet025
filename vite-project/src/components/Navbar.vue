<template>
  <nav class="sticky top-0 z-50 w-full backdrop-blur-xl bg-gradient-to-r from-[#0f172a]/70 via-[#1e293b]/70 to-[#0f172a]/70 border-b border-white/10 shadow-xl transition-all duration-300">
    <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-10">
      <div class="flex justify-between items-center h-16">
        <!-- Logo + Nom -->
        <router-link to="/" class="flex items-center gap-3">
          <img :src="Logo1" alt="E-Qanaouita Logo" class="h-9 w-auto drop-shadow-md" />
          <span class="text-2xl font-bold text-white tracking-wider font-sans hover:scale-105 transition-transform duration-200">
            E-Qanaouita
          </span>
        </router-link>

        <!-- Desktop Links -->
        <div class="hidden md:flex items-center space-x-6">
          <router-link
            to="/dashboard"
            :class="`${isActive('/dashboard') ? 'bg-blue-600 text-white' : 'text-white hover:bg-white/10'} px-4 py-2 rounded-md flex items-center gap-2`"
          >
            <Shield class="h-4 w-4" /> Dashboard
          </router-link>

          <router-link
            to="/trade"
            :class="`${isActive('/trade') ? 'bg-blue-600 text-white' : 'text-white hover:bg-white/10'} px-4 py-2 rounded-md flex items-center gap-2`"
          >
            <TrendingUp class="h-4 w-4" /> Trade
          </router-link>

          <router-link
            to="/profile"
            :class="`${isActive('/profile') ? 'bg-blue-600 text-white' : 'text-white hover:bg-white/10'} px-4 py-2 rounded-md flex items-center gap-2`"
          >
            <User class="h-4 w-4" /> Profile
          </router-link>

          <router-link
            to="/support"
            :class="`${isActive('/support') ? 'bg-blue-600 text-white' : 'text-white hover:bg-white/10'} px-4 py-2 rounded-md flex items-center gap-2`"
          >
            <HelpCircle class="h-4 w-4" /> Support
          </router-link>

          <!-- Balance Chip for Client Users -->
          <div
            v-if="auth.user?.role === 'client'"
            class="flex items-center space-x-1 px-2 py-1 rounded-full text-xs sm:text-sm text-white"
            :style="{ backgroundColor: 'rgba(59, 130, 246, 0.2)' }"
          >
            <template v-if="isBalanceLoading">
              <div class="flex items-center space-x-1">
                <div class="w-12 h-4 bg-white/20 rounded animate-pulse"></div>
              </div>
            </template>
            <template v-else>
              <span>Euro Balance: {{ formattedBalance }}</span>
            </template>
          </div>

          <!-- Auth state -->
          <div v-if="auth.isAuthenticated" class="flex items-center space-x-3 text-white">
            <div class="flex flex-col text-right">
              <span class="text-sm font-semibold truncate max-w-[140px]">{{ auth.user?.name }}</span>
              <span class="text-xs text-gray-300 truncate max-w-[140px]">{{ auth.user?.email }}</span>
            </div>
            <button @click="handleLogout" class="px-3 py-2 rounded-full border border-white/20 hover:bg-white/10 transition text-sm">
              Logout
            </button>
          </div>
          <template v-else>
            <router-link to="/signin" class="px-4 py-2 rounded-full border border-white/20 text-white hover:border-white hover:bg-white/10 transition">
              Sign In
            </router-link>
            <router-link
              to="/signup"
              class="px-5 py-2 rounded-full bg-gradient-to-r from-indigo-600 to-blue-500 hover:from-indigo-700 hover:to-blue-600 text-white font-semibold shadow-lg hover:scale-105 transition"
            >
              Sign Up
            </router-link>
          </template>
        </div>

        <!-- Mobile Menu Button -->
        <div class="md:hidden">
          <button @click="toggleMenu" class="p-2 rounded-full text-white hover:bg-white/10 hover:scale-105 transition">
            <component :is="isMenuOpen ? X : Menu" class="h-6 w-6" />
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div v-if="isMenuOpen" class="md:hidden bg-[#0f172a]/90 backdrop-blur-lg border-t border-white/10 px-6 py-4 space-y-3 transition-all">
      <router-link
        to="/dashboard"
        @click="closeMenu"
        :class="`${isActive('/dashboard') ? 'bg-blue-600 text-white' : 'text-white hover:bg-white/10'} block px-4 py-2 rounded-md flex items-center gap-2`"
      >
        <Shield class="h-5 w-5" /> Dashboard
      </router-link>

      <router-link
        to="/trade"
        @click="closeMenu"
        :class="`${isActive('/trade') ? 'bg-blue-600 text-white' : 'text-white hover:bg-white/10'} block px-4 py-2 rounded-md flex items-center gap-2`"
      >
        <TrendingUp class="h-5 w-5" /> Trade
      </router-link>

      <router-link
        to="/profile"
        @click="closeMenu"
        :class="`${isActive('/profile') ? 'bg-blue-600 text-white' : 'text-white hover:bg-white/10'} block px-4 py-2 rounded-md flex items-center gap-2`"
      >
        <User class="h-5 w-5" /> Profile
      </router-link>

      <router-link
        to="/support"
        @click="closeMenu"
        :class="`${isActive('/support') ? 'bg-blue-600 text-white' : 'text-white hover:bg-white/10'} block px-4 py-2 rounded-md flex items-center gap-2`"
      >
        <HelpCircle class="h-5 w-5" /> Support
      </router-link>

      <div v-if="auth.isAuthenticated" class="px-4 py-3 bg-white/5 rounded-lg text-white space-y-1">
        <div class="font-semibold">{{ auth.user?.name }}</div>
        <div class="text-xs text-gray-300">{{ auth.user?.email }}</div>
        <button @click="handleLogout" class="mt-2 w-full px-4 py-2 rounded-md border border-white/20 hover:bg-white/10 transition text-sm">
          Logout
        </button>
      </div>
      <template v-else>
        <router-link to="/signin" @click="closeMenu" class="block px-4 py-2 text-white border border-white/20 rounded-md hover:bg-white/10 transition">
          Sign In
        </router-link>

        <router-link to="/signup" @click="closeMenu" class="block px-4 py-2 text-white rounded-md bg-gradient-to-r from-indigo-600 to-blue-500 hover:from-indigo-700 hover:to-blue-600 shadow-md transition">
          Sign Up
        </router-link>
      </template>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { User, Shield, TrendingUp, HelpCircle, Menu, X } from 'lucide-vue-next';
import Logo1 from '../assets/Logo1.png';
import { formatEUR } from '../utils/formatEUR';
import { useAuthStore } from '@/stores/auth';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();

const isMenuOpen = ref(false);
const isBalanceLoading = ref(true);

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;
};
const closeMenu = () => {
  isMenuOpen.value = false;
};

const isActive = (path: string) => route.path === path;

onMounted(async () => {
  const timer = setTimeout(() => {
    isBalanceLoading.value = false;
    clearTimeout(timer);
  }, 500);
  if (!auth.user && auth.token) {
    await auth.fetchCurrentUser();
  }
});

const formattedBalance = computed(() => {
  try {
    return formatEUR((auth.user as any)?.euro_balance ?? 0);
  } catch {
    return '';
  }
});

async function handleLogout() {
  await auth.logout();
  closeMenu();
  router.push('/signin');
}
</script>

<style scoped>
/* Pas de styles additionnels pour l'instant, tout est géré via Tailwind classes */
</style>