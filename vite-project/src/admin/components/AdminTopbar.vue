<template>
  <header class="sticky top-0 z-30 h-16 bg-gray-800/95 backdrop-blur-sm border-b border-gray-700 flex items-center justify-between px-4 sm:px-6 shadow-lg">
    <div class="flex items-center space-x-4">
      <div class="flex items-center space-x-2">
        <div class="h-2 w-2 rounded-full animate-pulse" :style="{ backgroundColor: 'var(--accent-green)' }"></div>
        <span class="text-sm text-gray-400 hidden sm:inline">En ligne</span>
      </div>
    </div>

    <div class="flex items-center space-x-3">
      <div class="relative" ref="notifRef">
        <button
          class="relative p-2 rounded-lg hover:bg-gray-700/70 transition-colors"
          @click="toggleNotif"
          :title="pendingCount > 0 ? `${pendingCount} en attente de validation` : 'Aucune notification'"
        >
          <Bell class="h-5 w-5 text-gray-300" />
          <span
            v-if="pendingCount > 0"
            class="absolute -top-1 -right-1 text-[10px] font-semibold text-white rounded-full px-1.5 py-0.5"
            style="background-color: var(--accent-red);"
          >
            {{ pendingCount }}
          </span>
        </button>

        <transition name="fade">
          <div
            v-if="notifOpen"
            class="absolute right-0 mt-2 w-80 bg-gray-800 border border-gray-700 rounded-xl shadow-2xl py-2 z-50"
          >
            <div class="px-4 py-2 border-b border-gray-700 flex items-center justify-between">
              <span class="text-sm text-gray-200">Validations en attente</span>
              <span class="text-xs text-gray-400">{{ pendingUsers.length }} items</span>
            </div>
            <div v-if="pendingUsers.length === 0" class="p-4 text-sm text-gray-400">Aucune notification</div>
            <div v-else class="max-h-64 overflow-y-auto divide-y divide-gray-700">
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
        </transition>
      </div>
      <div class="relative" ref="menuRef">
        <button
          @click="toggleProfileMenu"
          class="flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2 rounded-lg hover:bg-gray-700/80 transition-all duration-200 border border-transparent hover:border-gray-600"
        >
          <div class="flex items-center space-x-2">
            <div
              class="h-9 w-9 rounded-full flex items-center justify-center shadow-lg"
              :style="{
                background: `linear-gradient(to bottom right, var(--blue), var(--blue-dark))`,
                boxShadow: 'var(--blue-ring-shadow)'
              }"
            >
              <Shield class="h-5 w-5 text-white" />
            </div>

            <div class="hidden lg:block text-left">
              <div class="text-sm font-semibold text-white">{{ currentUser.name }}</div>
              <div class="text-xs text-gray-400 truncate max-w-[150px]">{{ currentUser.email }}</div>
            </div>
          </div>

          <ChevronDown class="h-4 w-4 text-gray-400" :class="{ 'rotate-180': isProfileMenuOpen }" />
        </button>

        <!-- Dropdown -->
        <transition name="fade">
          <div
            v-if="isProfileMenuOpen"
            class="absolute right-0 mt-2 w-72 bg-gray-800 border border-gray-700 rounded-xl shadow-2xl py-2 z-50"
          >
            <div class="px-4 py-3 border-b border-gray-700 bg-gradient-to-r from-gray-800 to-gray-800/50" @click="viewProfile">
              <div class="flex items-center space-x-3">
                <div
                  class="h-12 w-12 rounded-full flex items-center justify-center shadow-lg"
                  :style="{
                    background: `linear-gradient(to bottom right, var(--blue), var(--blue-dark))`,
                    boxShadow: 'var(--blue-ring-shadow)'
                  }"
                >
                  <Shield class="h-7 w-7 text-white" />
                </div>
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-bold text-white truncate">{{ currentUser.name }}</div>
                  <div class="text-xs text-gray-400 truncate">{{ currentUser.email }}</div>
                  <div class="flex items-center space-x-1 mt-1">
                    <div class="h-1.5 w-1.5 rounded-full" :style="{ backgroundColor: 'var(--accent-green)' }"></div>
                    <span class="text-xs" :style="{ color: 'var(--accent-green)' }">Administrator</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="py-2">
              <button @click="viewProfile" class="w-full flex items-center space-x-3 px-4 py-2.5 text-sm text-gray-300 hover:bg-gray-700/80 hover:text-white transition-all duration-150">
                <User class="h-4 w-4" />
                <span class="font-medium">View profile</span>
              </button>

              <button class="w-full flex items-center space-x-3 px-4 py-2.5 text-sm text-gray-300 hover:bg-gray-700/80 hover:text-white transition-all duration-150">
                <Settings class="h-4 w-4" />
                <span class="font-medium">Settings</span>
              </button>
            </div>

            <div class="border-t border-gray-700 pt-2 mt-1">
              <button
                @click="logout"
                class="w-full flex items-center space-x-3 px-4 py-2.5 text-sm transition-all duration-150 group rounded-lg mx-2"
                :style="{ color: 'var(--accent-red)' }"
              >
                <LogOut class="h-4 w-4" />
                <span class="font-medium">Logout</span>
              </button>
            </div>
          </div>
        </transition>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { useRouter } from 'vue-router';
import { User, LogOut, Settings, ChevronDown, Shield, Bell } from 'lucide-vue-next';
import { useAuthStore } from '@/stores/auth';
import api from '@/services/api';

const router = useRouter();
const auth = useAuthStore();
const isProfileMenuOpen = ref(false);
const menuRef = ref<HTMLElement | null>(null);
const notifRef = ref<HTMLElement | null>(null);
const pendingCount = ref(0);
const loadingNotif = ref(false);
const notifOpen = ref(false);
let notifTimer: ReturnType<typeof setInterval> | null = null;

const currentUser = computed(() => {
  const u = auth.user;
  if (!u) return { name: 'Admin', email: '' };
  const name =
    (u.first_name || u.last_name) ? `${u.first_name ?? ''} ${u.last_name ?? ''}`.trim() : u.name ?? 'Admin';
  return { name: name || 'Admin', email: u.email ?? '' };
});
const pendingUsers = computed(() => (authPendingUsers.value ?? []).slice(0, 20));
const authPendingUsers = ref<any[]>([]);

function toggleProfileMenu() {
  isProfileMenuOpen.value = !isProfileMenuOpen.value;
}

function toggleNotif() {
  notifOpen.value = !notifOpen.value;
}

function goToUsers() {
  notifOpen.value = false;
  router.push('/admin/users');
}

async function logout() {
  await auth.logout();
  router.push('/signin');
}

function viewProfile() {
  isProfileMenuOpen.value = false;
  router.push('/admin/profile');
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
});
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity .15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>