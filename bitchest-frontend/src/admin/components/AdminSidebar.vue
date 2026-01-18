<template>
  <div>
    <!-- Mobile Sidebar Overlay -->
    <Transition name="mobile-sidebar">
      <div
        v-if="isMobileMenuOpen"
        class="md:hidden fixed inset-0 z-50 bg-gray-900/80 backdrop-blur-sm"
        @click="closeMobileSidebar"
      >
        <div
          class="fixed inset-y-0 left-0 w-[85vw] max-w-[300px] bg-gray-800 border-r border-gray-700 overflow-y-auto"
          @click.stop
        >
          <div class="flex flex-col h-full">
            <!-- Mobile Header -->
            <div class="p-4 flex items-center justify-center">
              <img :src="AdminLogo" alt="Admin Logo" class="h-auto w-32 object-contain" />
            </div>

            <!-- Profile Section Compact - Futuriste -->
            <div class="px-4 py-2 group relative">
              <button
                class="w-full flex items-center space-x-3 cursor-pointer rounded-xl px-3 py-2.5 transition-all duration-200 hover:bg-gray-700/30 hover:shadow-lg hover:shadow-blue-500/10 border border-transparent hover:border-blue-500/20"
                @click="toggleProfileMenu"
              >
                <div class="relative w-10 h-10 rounded-full overflow-hidden border-2 border-blue-500/30 shadow-lg flex-shrink-0">
                  <img
                    :src="adminLogoUrl"
                    alt="Admin Avatar"
                    class="w-full h-full object-cover"
                    @error="handleImageError"
                    @load="imageLoaded = true"
                  />
                  <div v-if="!imageLoaded" class="absolute inset-0 bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center">
                    <Shield class="h-5 w-5 text-white" />
                  </div>
                  <!-- Status indicator -->
                  <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-green-500 rounded-full border-2 border-gray-800 shadow-lg animate-pulse">
                    <div class="absolute inset-0 bg-green-400 rounded-full animate-ping opacity-75"></div>
                  </div>
                </div>
                <div class="flex-1 min-w-0 text-left">
                  <div class="flex items-center gap-2 mb-0.5">
                    <p class="text-sm font-semibold text-white truncate">{{ currentUser.name }}</p>
                    <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-green-500/20 border border-green-500/30">
                      <div class="h-1.5 w-1.5 rounded-full bg-green-400 animate-pulse"></div>
                      <span class="text-[10px] font-medium text-green-400 uppercase tracking-wider">Online</span>
                    </div>
                  </div>
                  <p class="text-xs text-gray-400 truncate">{{ currentUser.email }}</p>
                </div>
                <ChevronDown class="h-4 w-4 text-gray-400 transition-transform duration-200 flex-shrink-0" :class="{ 'rotate-180': isProfileMenuOpen }" />
              </button>
              <!-- Profile Menu Dropdown -->
              <Transition name="fade">
                <div
                  v-if="isProfileMenuOpen"
                  class="absolute left-4 right-4 top-full mt-2 bg-gray-700/95 backdrop-blur-sm rounded-lg border border-gray-600 shadow-xl z-50 overflow-hidden"
                  @click.stop
                >
                  <div class="px-4 py-3 border-b border-gray-600 bg-gradient-to-r from-gray-800 to-gray-800/50">
                    <div class="flex items-center space-x-3">
                      <div class="relative w-12 h-12 rounded-full overflow-hidden border-2 border-blue-500/30 shadow-lg flex-shrink-0">
                        <img
                          :src="adminLogoUrl"
                          alt="Admin Avatar"
                          class="w-full h-full object-cover"
                          @error="handleImageError"
                        />
                        <div v-if="!imageLoaded" class="absolute inset-0 bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center">
                          <Shield class="h-6 w-6 text-white" />
                        </div>
                        <div class="absolute -bottom-0.5 -right-0.5 w-4 h-4 bg-green-500 rounded-full border-2 border-gray-800 shadow-lg">
                          <div class="absolute inset-0 bg-green-400 rounded-full animate-ping opacity-75"></div>
                        </div>
                      </div>
                      <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                          <p class="text-sm font-bold text-white truncate">{{ currentUser.name }}</p>
                          <div class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-500/20 border border-green-500/30">
                            <div class="h-1.5 w-1.5 rounded-full bg-green-400 animate-pulse"></div>
                            <span class="text-[10px] font-medium text-green-400 uppercase tracking-wider">Online</span>
                          </div>
                        </div>
                        <p class="text-xs text-gray-400 truncate">{{ currentUser.email }}</p>
                        <div class="flex items-center space-x-1 mt-1.5">
                          <Shield class="h-3 w-3 text-blue-400" />
                          <span class="text-xs text-blue-400 font-medium">Administrator</span>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="py-2">
                    <button @click="handleViewProfile" class="w-full flex items-center space-x-3 px-4 py-2.5 text-sm text-gray-300 hover:bg-gray-700/80 hover:text-white transition-all duration-150">
                      <User class="h-4 w-4" />
                      <span class="font-medium">View profile</span>
                    </button>
                <button
                  @click="handleSecurity"
                  class="w-full flex items-center space-x-3 px-4 py-2.5 text-sm text-gray-300 hover:bg-gray-700/80 hover:text-white transition-all duration-150"
                >
                  <Lock class="h-4 w-4" />
                  <span class="font-medium">Security</span>
                </button>
                  </div>
                  <div class="border-t border-gray-700 pt-2 mt-1">
                    <button
                      @click="handleLogout"
                      class="w-full flex items-center space-x-3 px-4 py-2.5 text-sm transition-all duration-150 group rounded-lg mx-2"
                      :style="{ color: 'var(--accent-red)' }"
                    >
                      <LogOut class="h-4 w-4" />
                      <span class="font-medium">Logout</span>
                    </button>
                  </div>
                </div>
              </Transition>
            </div>

            <!-- Notifications Section with Dropdown -->
            <div class="px-4 py-2 relative" ref="notifRef">
              <button
                @click.stop="toggleNotif"
                class="relative w-full flex items-center space-x-3 px-4 py-2.5 rounded-xl transition-all duration-200 hover:bg-gray-700/50 group"
                :title="pendingCount > 0 ? `${pendingCount} en attente de validation` : 'Aucune notification'"
              >
                <Bell class="h-5 w-5 text-gray-300" />
                <span class="font-medium text-gray-300">Notifications</span>
                <Transition name="bounce">
                  <span
                    v-if="pendingCount > 0"
                    class="ml-auto text-xs font-semibold text-white rounded-full px-2 py-0.5"
                    style="background-color: var(--accent-red);"
                  >
                    {{ pendingCount }}
                  </span>
                </Transition>
              </button>
              <!-- Notifications Dropdown -->
              <Transition name="fade">
                <div
                  v-if="notifOpen"
                  class="absolute left-4 right-4 top-full mt-2 bg-gray-800 border border-gray-700 rounded-xl shadow-2xl py-2 z-50 max-h-64 overflow-y-auto"
                  @click.stop
                >
                  <div class="px-4 py-2 border-b border-gray-700 flex items-center justify-between sticky top-0 bg-gray-800">
                    <span class="text-sm text-gray-200">Validations en attente</span>
                    <span class="text-xs text-gray-400">{{ pendingUsers.length }} items</span>
                  </div>
                  <div v-if="pendingUsers.length === 0" class="p-4 text-sm text-gray-400">Aucune notification</div>
                  <div v-else class="divide-y divide-gray-700">
                    <button
                      v-for="u in pendingUsers"
                      :key="u.id"
                      class="w-full text-left px-4 py-2 hover:bg-gray-700/60 transition flex items-center justify-between"
                      @click="goToUsers"
                    >
                      <div>
                        <div class="text-white text-sm font-semibold">{{ u.first_name || '' }} {{ u.last_name || '' }}</div>
                        <div class="text-xs text-gray-400">{{ u.email }}</div>
                      </div>
                      <span class="text-[11px] px-2 py-1 rounded-full" style="background-color: var(--accent-orange); color: #111827">
                        pending
                      </span>
                    </button>
                  </div>
                </div>
              </Transition>
            </div>

            <!-- Navigation Section -->
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto pb-20">
              <RouterLink
                v-for="item in sidebarItems"
                :key="item.path"
                :to="item.path"
                @click="closeMobileSidebar"
                class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 touch-manipulation"
                :class="isActive(item.path) 
                  ? 'bg-gray-700 text-white shadow-lg' 
                  : 'text-gray-300 active:bg-gray-700/50 hover:bg-gray-700/50 hover:text-white'"
              >
                <span :style="isActive(item.path) ? { color: 'var(--blue)' } : {}">
                  <component :is="item.icon" class="h-5 w-5" />
                </span>
                <span class="font-medium">{{ item.label }}</span>
              </RouterLink>
            </nav>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Desktop Sidebar -->
    <aside
      class="hidden md:flex flex-col fixed top-0 left-0 h-full w-64 bg-gray-800 border-r border-gray-700 z-40 overflow-y-auto"
    >
      <div class="flex flex-col h-full">
        <!-- Sidebar Header - Logo agrandi sans bordure -->
        <div class="p-6 flex items-center justify-center">
          <img :src="AdminLogo" alt="Admin Logo" class="h-auto w-full max-w-[160px] object-contain" />
        </div>

      

        <!-- Profile Section avec Menu - Futuriste -->
        <div class="px-4 py-2 group relative" ref="menuRef">
          <button
            class="w-full flex items-center space-x-3 cursor-pointer rounded-xl px-3 py-2.5 transition-all duration-200 hover:bg-gray-700/30 hover:shadow-lg hover:shadow-blue-500/10 border border-transparent hover:border-blue-500/20"
            @click.stop="toggleProfileMenu"
          >
            <div class="relative w-10 h-10 rounded-full overflow-hidden border-2 border-blue-500/30 shadow-lg flex-shrink-0">
              <img
                :src="adminLogoUrl"
                alt="Admin Avatar"
                class="w-full h-full object-cover"
                @error="handleImageError"
                @load="imageLoaded = true"
              />
              <div v-if="!imageLoaded" class="absolute inset-0 bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center">
                <Shield class="h-5 w-5 text-white" />
              </div>
              <!-- Status indicator -->
              <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-green-500 rounded-full border-2 border-gray-800 shadow-lg animate-pulse">
                <div class="absolute inset-0 bg-green-400 rounded-full animate-ping opacity-75"></div>
              </div>
            </div>
            <div class="flex-1 min-w-0 text-left">
              <div class="flex items-center gap-2 mb-0.5">
                <p class="text-sm font-semibold text-white truncate">{{ currentUser.name }}</p>
                <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-green-500/20 border border-green-500/30">
                  <div class="h-1.5 w-1.5 rounded-full bg-green-400 animate-pulse"></div>
                  <span class="text-[10px] font-medium text-green-400 uppercase tracking-wider">Online</span>
                </div>
              </div>
              <p class="text-xs text-gray-400 truncate">{{ currentUser.email }}</p>
            </div>
            <ChevronDown class="h-4 w-4 text-gray-400 transition-transform duration-200 flex-shrink-0" :class="{ 'rotate-180': isProfileMenuOpen }" />
          </button>
          <!-- Profile Menu Dropdown -->
          <Transition name="fade">
            <div
              v-if="isProfileMenuOpen"
              class="absolute left-4 right-4 top-full mt-2 bg-gray-700/95 backdrop-blur-sm rounded-lg border border-gray-600 shadow-xl z-50 overflow-hidden"
              @click.stop
            >
              <div class="px-4 py-3 border-b border-gray-600 bg-gradient-to-r from-gray-800 to-gray-800/50">
                <div class="flex items-center space-x-3">
                  <div class="relative w-12 h-12 rounded-full overflow-hidden border-2 border-blue-500/30 shadow-lg flex-shrink-0">
                    <img
                      :src="adminLogoUrl"
                      alt="Admin Avatar"
                      class="w-full h-full object-cover"
                      @error="handleImageError"
                    />
                    <div v-if="!imageLoaded" class="absolute inset-0 bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center">
                      <Shield class="h-6 w-6 text-white" />
                    </div>
                    <div class="absolute -bottom-0.5 -right-0.5 w-4 h-4 bg-green-500 rounded-full border-2 border-gray-800 shadow-lg">
                      <div class="absolute inset-0 bg-green-400 rounded-full animate-ping opacity-75"></div>
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                      <p class="text-sm font-bold text-white truncate">{{ currentUser.name }}</p>
                      <div class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-500/20 border border-green-500/30">
                        <div class="h-1.5 w-1.5 rounded-full bg-green-400 animate-pulse"></div>
                        <span class="text-[10px] font-medium text-green-400 uppercase tracking-wider">Online</span>
                      </div>
                    </div>
                    <p class="text-xs text-gray-400 truncate">{{ currentUser.email }}</p>
                    <div class="flex items-center space-x-1 mt-1.5">
                      <Shield class="h-3 w-3 text-blue-400" />
                      <span class="text-xs text-blue-400 font-medium">Administrator</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="py-2">
                <button @click="handleViewProfile" class="w-full flex items-center space-x-3 px-4 py-2.5 text-sm text-gray-300 hover:bg-gray-700/80 hover:text-white transition-all duration-150">
                  <User class="h-4 w-4" />
                  <span class="font-medium">View profile</span>
                </button>
                <button
                  @click="handleSecurity"
                  class="w-full flex items-center space-x-3 px-4 py-2.5 text-sm text-gray-300 hover:bg-gray-700/80 hover:text-white transition-all duration-150"
                >
                  <Lock class="h-4 w-4" />
                  <span class="font-medium">Security</span>
                </button>
              </div>
              <div class="border-t border-gray-700 pt-2 mt-1">
                <button
                  @click="handleLogout"
                  class="w-full flex items-center space-x-3 px-4 py-2.5 text-sm transition-all duration-150 group rounded-lg mx-2"
                  :style="{ color: 'var(--accent-red)' }"
                >
                  <LogOut class="h-4 w-4" />
                  <span class="font-medium">Logout</span>
                </button>
              </div>
            </div>
          </Transition>
        </div>

        <!-- Notifications Section with Dropdown -->
        <div class="px-4 py-2 relative" ref="notifRef">
          <button
            @click.stop="toggleNotif"
            class="relative w-full flex items-center space-x-3 px-4 py-2.5 rounded-xl transition-all duration-200 hover:bg-gray-700/50 group"
            :title="pendingCount > 0 ? `${pendingCount} en attente de validation` : 'Aucune notification'"
          >
            <Bell class="h-5 w-5 text-gray-300" />
            <span class="font-medium text-gray-300">Notifications</span>
            <Transition name="bounce">
              <span
                v-if="pendingCount > 0"
                class="ml-auto text-xs font-semibold text-white rounded-full px-2 py-0.5"
                style="background-color: var(--accent-red);"
              >
                {{ pendingCount }}
              </span>
            </Transition>
          </button>
          <!-- Notifications Dropdown -->
          <Transition name="fade">
            <div
              v-if="notifOpen"
              class="absolute left-4 right-4 top-full mt-2 bg-gray-800 border border-gray-700 rounded-xl shadow-2xl py-2 z-50 max-h-64 overflow-y-auto"
              @click.stop
            >
              <div class="px-4 py-2 border-b border-gray-700 flex items-center justify-between sticky top-0 bg-gray-800">
                <span class="text-sm text-gray-200">Validations en attente</span>
                <span class="text-xs text-gray-400">{{ pendingUsers.length }} items</span>
              </div>
              <div v-if="pendingUsers.length === 0" class="p-4 text-sm text-gray-400">Aucune notification</div>
              <div v-else class="divide-y divide-gray-700">
                <button
                  v-for="u in pendingUsers"
                  :key="u.id"
                  class="w-full text-left px-4 py-2 hover:bg-gray-700/60 transition flex items-center justify-between"
                  @click="goToUsers"
                >
                  <div>
                    <div class="text-white text-sm font-semibold">{{ u.first_name || '' }} {{ u.last_name || '' }}</div>
                    <div class="text-xs text-gray-400">{{ u.email }}</div>
                  </div>
                  <span class="text-[11px] px-2 py-1 rounded-full" style="background-color: var(--accent-orange); color: #111827">
                    pending
                  </span>
                </button>
              </div>
            </div>
          </Transition>
        </div>

        <!-- Navigation Items -->
        <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
          <RouterLink
            v-for="item in sidebarItems"
            :key="item.path"
            :to="item.path"
            class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200"
            :class="isActive(item.path) 
              ? 'bg-gray-700 text-white shadow-lg' 
              : 'text-gray-300 hover:bg-gray-700/50 hover:text-white'"
          >
            <span :style="isActive(item.path) ? { color: 'var(--blue)' } : {}">
              <component :is="item.icon" class="h-5 w-5" />
            </span>
            <span class="font-medium">{{ item.label }}</span>
          </RouterLink>
        </nav>
      </div>
    </aside>

  </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onBeforeUnmount, watch } from 'vue';
import { useRoute, RouterLink, useRouter } from 'vue-router';
import { LayoutDashboard, Users, TrendingUp, FileText, Shield, X, Bell, User, LogOut, Lock, ChevronDown } from 'lucide-vue-next';
import AdminLogo from '../../assets/ADMIN.png';
import { useAuthStore } from '@/stores/auth';
import api from '@/services/api';

interface SidebarItem {
  path: string;
  label: string;
  icon: any;
}

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const isProfileMenuOpen = ref(false);
const notifOpen = ref(false);
const isMobileMenuOpen = ref(false);
const pendingCount = ref(0);
const loadingNotif = ref(false);
const authPendingUsers = ref<any[]>([]);
const menuRef = ref<HTMLElement | null>(null);
const notifRef = ref<HTMLElement | null>(null);
let notifTimer: ReturnType<typeof setInterval> | null = null;

const props = defineProps<{
  isMobileOpen?: boolean;
}>();

const emit = defineEmits<{
  'update:isMobileOpen': [value: boolean];
  'close': [];
  'toggle-mobile-menu': [];
}>();

// Synchroniser isMobileMenuOpen avec isMobileOpen
watch(() => props.isMobileOpen, (newVal) => {
  isMobileMenuOpen.value = newVal ?? false;
});

const sidebarItems: SidebarItem[] = [
  { path: '/admin', label: 'Dashboard', icon: LayoutDashboard },
  { path: '/admin/users', label: 'Users', icon: Users },
  { path: '/admin/market', label: 'Market', icon: TrendingUp },
  { path: '/admin/transactions', label: 'Transactions', icon: FileText },
];

const currentUser = computed(() => {
  const u = auth.user;
  if (!u) return { name: 'Admin', email: '' };
  const name =
    (u.first_name || u.last_name) ? `${u.first_name ?? ''} ${u.last_name ?? ''}`.trim() : u.name ?? 'Admin';
  return { name: name || 'Admin', email: u.email ?? '' };
});

// Admin logo URL
const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000';
const adminLogoUrl = computed(() => `${baseUrl}/images/adminLogo.png`);
const imageLoaded = ref(false);

function handleImageError(event: Event) {
  const target = event.target as HTMLImageElement;
  if (target) {
    imageLoaded.value = false;
  }
}

const pendingUsers = computed(() => (authPendingUsers.value ?? []).slice(0, 20));

function isActive(path: string) {
  if (path === '/admin') {
    return route.path === '/admin';
  }
  return route.path.startsWith(path);
}

function closeMobileSidebar() {
  isMobileMenuOpen.value = false;
  emit('update:isMobileOpen', false);
  emit('close');
  // Restore body scroll when closing mobile menu
  document.body.style.overflow = '';
}

function toggleMobileMenu() {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
  emit('update:isMobileOpen', isMobileMenuOpen.value);
  emit('toggle-mobile-menu');
}

function toggleProfileMenu() {
  isProfileMenuOpen.value = !isProfileMenuOpen.value;
}

function toggleNotif() {
  notifOpen.value = !notifOpen.value;
}

function handleViewProfile() {
  isProfileMenuOpen.value = false;
  closeMobileSidebar();
  router.push({ path: '/admin/profile', query: { tab: 'profile' } });
}

function handleSecurity() {
  isProfileMenuOpen.value = false;
  closeMobileSidebar();
  router.push({ path: '/admin/profile', query: { tab: 'security' } });
}

async function handleLogout() {
  isProfileMenuOpen.value = false;
  closeMobileSidebar();
  await auth.logout();
  router.push('/signin');
}

function goToUsers() {
  notifOpen.value = false;
  closeMobileSidebar();
  router.push('/admin/users');
}

async function fetchPending() {
  if (loadingNotif.value) return;
  loadingNotif.value = true;
  try {
    if (!auth.token) {
      pendingCount.value = 0;
      authPendingUsers.value = [];
      return;
    }
    const users = await api.getAdminUsers();
    const pending = users.filter((u) => u.status === 'pending_validation');
    pendingCount.value = pending.length;
    authPendingUsers.value = pending;
  } catch (_) {
    // silent fail to avoid blocking UI
  } finally {
    loadingNotif.value = false;
  }
}

const handleClickOutside = (e: MouseEvent) => {
  const target = e.target as Node;
  if (menuRef.value && !menuRef.value.contains(target)) {
    isProfileMenuOpen.value = false;
  }
  if (notifRef.value && !notifRef.value.contains(target)) {
    notifOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside);
  (async () => {
    if (!auth.user && auth.token) {
      try { await auth.fetchCurrentUser(); } catch (_) {}
    }
    await fetchPending();
    notifTimer = setInterval(fetchPending, 30000);
  })();
});

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleClickOutside);
  if (notifTimer) clearInterval(notifTimer);
  // Ensure body scroll is restored
  document.body.style.overflow = '';
});
</script>

<style scoped>
/* Mobile Sidebar Transition */
.mobile-sidebar-enter-active,
.mobile-sidebar-leave-active {
  transition: opacity 0.3s ease;
}

.mobile-sidebar-enter-active .fixed.inset-y-0,
.mobile-sidebar-leave-active .fixed.inset-y-0 {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.mobile-sidebar-enter-from .fixed.inset-y-0,
.mobile-sidebar-leave-to .fixed.inset-y-0 {
  transform: translateX(-100%);
}

.mobile-sidebar-enter-from,
.mobile-sidebar-leave-to {
  opacity: 0;
}

.mobile-sidebar-enter-to .fixed.inset-y-0,
.mobile-sidebar-leave-from .fixed.inset-y-0 {
  transform: translateX(0);
}

/* Fade Transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-5px);
}

/* Bounce Transition */
.bounce-enter-active {
  animation: bounce-in 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.bounce-leave-active {
  animation: bounce-out 0.2s ease-in;
}

@keyframes bounce-in {
  0% {
    transform: scale(0) rotate(-180deg);
    opacity: 0;
  }
  50% {
    transform: scale(1.3) rotate(10deg);
  }
  100% {
    transform: scale(1) rotate(0deg);
    opacity: 1;
  }
}

@keyframes bounce-out {
  0% {
    transform: scale(1) rotate(0deg);
    opacity: 1;
  }
  100% {
    transform: scale(0) rotate(180deg);
    opacity: 0;
  }
}
</style>
