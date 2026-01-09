<template>
  <nav class="sticky top-0 z-50 w-full backdrop-blur-xl bg-gradient-to-r from-[#0f172a]/70 via-[#1e293b]/70 to-[#0f172a]/70 border-b border-white/10 shadow-xl transition-all duration-300">
    <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-10">
      <div class="flex items-center justify-center h-16 relative">
        <!-- Logo (With spacing from left) -->
        <router-link to="/" class="absolute left-4 sm:left-6 flex items-center">
          <img :src="BitchestLogo" alt="Bitchest Logo" class="h-12 w-auto drop-shadow-md" />
        </router-link>

        <!-- Desktop Links (Centered) -->
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
            to="/support"
            :class="`${isActive('/support') ? 'bg-blue-600 text-white' : 'text-white hover:bg-white/10'} px-4 py-2 rounded-md flex items-center gap-2`"
          >
            <HelpCircle class="h-4 w-4" /> Support
          </router-link>

          <!-- Auth state with Avatar Dropdown (Absolute Right for Desktop) -->
          <div v-if="auth.isAuthenticated" class="hidden md:flex absolute right-0 items-center space-x-3">
            <!-- Notifications -->
            <NotificationDropdown v-if="auth.user?.role === 'client'" ref="notificationDropdownRef" />

            <!-- Balance Chip for Client Users -->
            <div
              v-if="auth.user?.role === 'client'"
              class="relative group"
            >
              <div
                class="flex items-center space-x-1 px-3 py-1.5 rounded-full text-xs sm:text-sm text-white cursor-pointer transition-all duration-200"
                :style="{ backgroundColor: 'rgba(59, 130, 246, 0.2)' }"
              >
                <template v-if="isBalanceLoading">
                  <div class="flex items-center space-x-1">
                    <div class="w-12 h-4 bg-white/20 rounded animate-pulse"></div>
                  </div>
                </template>
                <template v-else>
                  <span class="group-hover:hidden">Balance</span>
                  <div class="hidden group-hover:flex items-center space-x-1.5 animate-fade-in">
                    <span :style="{ color: accentGreen }" class="text-base font-semibold">€</span>
                    <span :style="{ color: accentGreen }" class="font-semibold">{{ formattedBalance.replace('€', '').trim() }}</span>
                  </div>
                </template>
              </div>
            </div>

            <!-- Avatar Button with User Name -->
            <div ref="avatarMenuRef" class="relative">
              <div class="relative group">
                <button 
                  @click="toggleProfileMenu" 
                  class="flex items-center space-x-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-900 rounded-full transition-all px-2 py-1 hover:bg-white/5"
                >
                  <div class="hidden lg:flex text-right text-white">
                    <span class="text-sm font-semibold truncate max-w-[150px]">{{ userDisplayName }}</span>
                  </div>
                  <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-white/20 hover:border-white/40 transition-colors bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center">
                    <img 
                      v-if="profilePictureUrl" 
                      :src="profilePictureUrl" 
                      :alt="auth.user?.name || 'User'" 
                      class="w-full h-full object-cover"
                      @error="handleImageError"
                    />
                    <span v-else class="text-white font-semibold text-sm">
                      {{ (auth.user?.name || 'U').charAt(0).toUpperCase() }}
                    </span>
                  </div>
                  <ChevronDown class="h-4 w-4 text-white" :class="{ 'rotate-180': isProfileMenuOpen }" />
                </button>
                <!-- Tooltip for User Name -->
                <div class="absolute right-0 top-full mt-2 px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg shadow-xl text-sm text-white whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50">
                  {{ userDisplayName }}
                </div>
              </div>

              <!-- Dropdown Menu -->
              <div 
                v-if="isProfileMenuOpen"
                class="absolute right-0 mt-2 w-56 bg-[#1e293b]/95 backdrop-blur-lg border border-white/10 rounded-lg shadow-xl py-1 z-50"
                @click.stop
              >
                <!-- User Info in Dropdown -->
                <div class="px-4 py-3 border-b border-white/10">
                  <div class="text-sm font-semibold text-white">{{ userDisplayName }}</div>
                </div>
                <router-link
                  to="/profile"
                  @click="closeProfileMenu"
                  class="dropdown-menu-item block px-4 py-2.5 text-gray-300 hover:text-white flex items-center gap-2 transition-all duration-150"
                >
                  <User class="h-4 w-4" />
                  <span class="font-medium">Profile</span>
                </router-link>
                <router-link
                  v-if="auth.user?.role === 'client'"
                  to="/app/portfolio"
                  @click="closeProfileMenu"
                  class="dropdown-menu-item block px-4 py-2.5 text-gray-300 hover:text-white flex items-center gap-2 transition-all duration-150"
                >
                  <Wallet class="h-4 w-4" />
                  <span class="font-medium">Portfolio</span>
                </router-link>
                <button
                  @click="handleLogout"
                  class="w-full text-left px-4 py-2 text-white hover:bg-red-600/20 flex items-center gap-2 transition-colors"
                >
                  <LogOut class="h-4 w-4" />
                  <span>Logout</span>
                </button>
              </div>
            </div>
          </div>
          <template v-else>
            <div class="hidden md:flex absolute right-0 items-center space-x-3">
              <router-link to="/signin" class="px-4 py-2 rounded-full border border-white/20 text-white hover:border-white hover:bg-white/10 transition">
                Sign In
              </router-link>
              <router-link
                to="/signup"
                class="px-5 py-2 rounded-full bg-gradient-to-r from-indigo-600 to-blue-500 hover:from-indigo-700 hover:to-blue-600 text-white font-semibold shadow-lg hover:scale-105 transition"
              >
                Sign Up
              </router-link>
            </div>
          </template>
        </div>

        <!-- Mobile Menu Button (Absolute Right) -->
        <div v-if="auth.isAuthenticated" class="md:hidden absolute right-12 flex items-center space-x-2">
          <!-- Mobile Notifications -->
          <NotificationDropdown v-if="auth.user?.role === 'client'" />
        </div>
        <div class="md:hidden absolute right-0">
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
        to="/support"
        @click="closeMenu"
        :class="`${isActive('/support') ? 'bg-blue-600 text-white' : 'text-white hover:bg-white/10'} block px-4 py-2 rounded-md flex items-center gap-2`"
      >
        <HelpCircle class="h-5 w-5" /> Support
      </router-link>

      <div v-if="auth.isAuthenticated" class="px-4 py-3 bg-white/5 rounded-lg text-white space-y-2">
        <div class="flex items-center space-x-3 pb-2 border-b border-white/10">
          <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-white/20 bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center">
            <img 
              v-if="profilePictureUrl" 
              :src="profilePictureUrl" 
              :alt="auth.user?.name || 'User'" 
              class="w-full h-full object-cover"
              @error="handleImageError"
            />
            <span v-else class="text-white font-semibold text-sm">
              {{ (auth.user?.name || 'U').charAt(0).toUpperCase() }}
            </span>
          </div>
          <div>
            <div class="font-semibold">{{ userDisplayName }}</div>
          </div>
        </div>
        <router-link
          to="/profile"
          @click="closeMenu"
          :class="`${isActive('/profile') ? 'bg-blue-600 text-white' : 'text-white hover:bg-white/10'} block px-4 py-2 rounded-md flex items-center gap-2`"
        >
          <User class="h-4 w-4" /> Profile
        </router-link>
        <router-link
          v-if="auth.user?.role === 'client'"
          to="/app/portfolio"
          @click="closeMenu"
          :class="`${isActive('/app/portfolio') ? 'bg-blue-600 text-white' : 'text-white hover:bg-white/10'} block px-4 py-2 rounded-md flex items-center gap-2`"
        >
          <Wallet class="h-4 w-4" /> Portfolio
        </router-link>
        <button @click="handleLogout" class="w-full px-4 py-2 rounded-md border border-red-600/50 hover:bg-red-600/20 transition text-sm text-white flex items-center gap-2">
          <LogOut class="h-4 w-4" /> Logout
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
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { User, Shield, TrendingUp, HelpCircle, Menu, X, ChevronDown, Wallet, LogOut } from 'lucide-vue-next';
import BitchestLogo from '../assets/bitchest_logo.png';
import { formatEUR } from '../utils/formatEUR';
import { useAuthStore } from '@/stores/auth';
import NotificationDropdown from './NotificationDropdown.vue';
import { useThemeColors } from '@/hooks/useThemeColors';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const { accentGreen } = useThemeColors();

const isMenuOpen = ref(false);
const isProfileMenuOpen = ref(false);
const isBalanceLoading = ref(true);
const avatarMenuRef = ref<HTMLElement | null>(null);

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;
  if (isMenuOpen.value) {
    isProfileMenuOpen.value = false;
  }
};
const closeMenu = () => {
  isMenuOpen.value = false;
};

const toggleProfileMenu = () => {
  isProfileMenuOpen.value = !isProfileMenuOpen.value;
  if (isProfileMenuOpen.value) {
    isMenuOpen.value = false;
  }
};
const closeProfileMenu = () => {
  isProfileMenuOpen.value = false;
};

// Close dropdown when clicking outside
const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement;
  if (avatarMenuRef.value && !avatarMenuRef.value.contains(target)) {
    isProfileMenuOpen.value = false;
  }
};

const isActive = (path: string) => route.path === path;

// Helper function to check if a value is a valid image path
function isValidImagePath(value: any): boolean {
  if (!value) return false;
  if (typeof value !== 'string') return false;
  const trimmed = value.trim();
  if (trimmed === '' || trimmed === 'null' || trimmed === 'undefined' || trimmed === 'NULL' || trimmed === 'UNDEFINED') return false;
  return true;
}

// Profile picture URL
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

// User display name (first_name + last_name or name)
const userDisplayName = computed(() => {
  const user = auth.user;
  if (!user) return 'User';
  if (user.first_name || user.last_name) {
    return `${user.first_name || ''} ${user.last_name || ''}`.trim();
  }
  return user.name || 'User';
});

function handleImageError(event: Event) {
  const img = event.target as HTMLImageElement;
  // If image fails to load, hide it - the placeholder will show
  if (img) {
    img.style.display = 'none';
  }
}

onMounted(async () => {
  const timer = setTimeout(() => {
    isBalanceLoading.value = false;
    clearTimeout(timer);
  }, 500);
  if (!auth.user && auth.token) {
    await auth.fetchCurrentUser();
  }
  // Add click outside listener
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  // Remove click outside listener
  document.removeEventListener('click', handleClickOutside);
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
  closeProfileMenu();
  router.push('/signin');
}
</script>

<style scoped>
.dropdown-menu-item:hover {
  background-color: rgba(53, 167, 255, 0.15);
}

@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateX(-4px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.2s ease-out;
}
</style>