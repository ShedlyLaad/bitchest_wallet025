<template>
  <div class="relative" ref="dropdownRef">
    <button 
      @click="toggleDropdown"
      class="relative p-2 rounded-lg text-white hover:bg-white/10 transition-all focus:outline-none focus:ring-2 focus:ring-blue-500"
      title="Notifications"
    >
      <Bell class="h-5 w-5" :class="{ 'animate-pulse': unreadCount > 0 }" />
      <Transition name="bounce">
        <span 
          v-if="unreadCount > 0"
          class="absolute -top-1 -right-1 flex items-center justify-center min-w-[20px] h-5 px-1.5 bg-red-500 text-white text-xs font-bold rounded-full border-2 border-gray-900 animate-bounce"
        >
          {{ unreadCount > 99 ? '99+' : unreadCount }}
        </span>
      </Transition>
    </button>

    <!-- Dropdown -->
    <Transition name="dropdown">
      <div
        v-if="isOpen"
        class="absolute right-0 mt-2 w-[420px] bg-gradient-to-br from-gray-900/95 via-gray-800/95 to-gray-900/95 backdrop-blur-2xl rounded-2xl border border-white/20 shadow-2xl z-50 max-h-[600px] flex flex-col overflow-hidden ring-1 ring-white/10"
        @click.stop
      >
        <!-- Header avec effet glassmorphism -->
        <div class="px-5 py-4 border-b border-white/10 flex items-center justify-between bg-gradient-to-r from-blue-500/10 via-purple-500/5 to-blue-500/10 backdrop-blur-sm">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500/20 to-purple-500/20 border border-white/20 flex items-center justify-center backdrop-blur-sm">
              <Bell class="h-5 w-5 text-blue-400" />
            </div>
            <div>
              <h3 class="text-lg font-bold text-white tracking-tight">Notifications</h3>
              <p v-if="unreadCount > 0" class="text-xs text-blue-400 font-medium">{{ unreadCount }} unread</p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <button
              v-if="unreadCount > 0"
              @click="markAllAsRead"
              class="px-3 py-1.5 text-xs font-semibold text-blue-400 hover:text-blue-300 bg-blue-500/10 hover:bg-blue-500/20 border border-blue-500/30 rounded-lg transition-all duration-200 backdrop-blur-sm"
            >
              Mark all read
            </button>
            <button
              @click="closeDropdown"
              class="p-2 text-gray-400 hover:text-white transition-all rounded-lg hover:bg-white/10 border border-transparent hover:border-white/20"
            >
              <X class="h-4 w-4" />
            </button>
          </div>
        </div>

        <!-- Notifications List -->
        <div class="overflow-y-auto flex-1">
          <div v-if="loading && notifications.length === 0" class="p-12 text-center">
            <div class="relative mx-auto w-16 h-16 mb-4">
              <div class="absolute inset-0 border-4 border-blue-500/20 rounded-full"></div>
              <div class="absolute inset-0 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
            </div>
            <p class="text-gray-400 text-sm font-medium">Loading notifications...</p>
          </div>
          
          <div v-else-if="notifications.length === 0" class="p-12 text-center">
            <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gradient-to-br from-gray-700/50 to-gray-800/50 border border-white/10 flex items-center justify-center backdrop-blur-sm">
              <Bell class="h-10 w-10 text-gray-500 opacity-50" />
            </div>
            <p class="text-gray-400 text-sm font-medium">No notifications</p>
            <p class="text-gray-500 text-xs mt-1">You're all caught up!</p>
          </div>

          <div v-else class="divide-y divide-white/5">
            <div
              v-for="notification in notifications"
              :key="notification.id"
              @click="handleNotificationClick(notification)"
              :class="[
                'group relative p-4 hover:bg-white/5 transition-all duration-300 cursor-pointer border-l-4 backdrop-blur-sm',
                notification.is_read 
                  ? 'border-transparent bg-white/2 hover:bg-white/5' 
                  : notification.type === 'profit'
                    ? 'border-green-500/60 bg-gradient-to-r from-green-500/10 to-transparent hover:from-green-500/15'
                    : notification.type === 'loss'
                      ? 'border-red-500/60 bg-gradient-to-r from-red-500/10 to-transparent hover:from-red-500/15'
                      : notification.type === 'level_up'
                        ? 'border-purple-500/60 bg-gradient-to-r from-purple-500/20 via-pink-500/10 to-transparent hover:from-purple-500/25'
                        : 'border-blue-500/60 bg-gradient-to-r from-blue-500/10 to-transparent hover:from-blue-500/15'
              ]"
            >
              <!-- Animated background gradient on hover -->
              <div 
                :class="[
                  'absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300',
                  notification.type === 'profit'
                    ? 'bg-gradient-to-r from-green-500/5 to-transparent'
                    : notification.type === 'loss'
                      ? 'bg-gradient-to-r from-red-500/5 to-transparent'
                      : notification.type === 'level_up'
                        ? 'bg-gradient-to-r from-purple-500/10 via-pink-500/5 to-transparent'
                        : 'bg-gradient-to-r from-blue-500/5 to-transparent'
                ]"
              ></div>
              
              <div class="relative flex items-start gap-4">
                <!-- Icon avec effet glassmorphism -->
                <div
                  :class="[
                    'w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 border backdrop-blur-sm transition-all duration-300 group-hover:scale-110',
                    notification.type === 'profit'
                      ? 'bg-gradient-to-br from-green-500/30 to-green-600/20 text-green-400 border-green-500/40 shadow-lg shadow-green-500/20'
                      : notification.type === 'loss'
                        ? 'bg-gradient-to-br from-red-500/30 to-red-600/20 text-red-400 border-red-500/40 shadow-lg shadow-red-500/20'
                        : notification.type === 'level_up'
                          ? 'bg-gradient-to-br from-purple-500/40 via-pink-500/30 to-purple-600/20 text-purple-300 border-purple-500/50 shadow-lg shadow-purple-500/30 animate-pulse'
                          : 'bg-gradient-to-br from-blue-500/30 to-blue-600/20 text-blue-400 border-blue-500/40 shadow-lg shadow-blue-500/20'
                  ]"
                >
                  <TrendingUp v-if="notification.type === 'profit'" class="h-6 w-6" />
                  <TrendingDown v-else-if="notification.type === 'loss'" class="h-6 w-6" />
                  <div v-else-if="notification.type === 'level_up'" class="relative">
                    <span class="text-2xl">⭐</span>
                    <div class="absolute -top-1 -right-1 w-3 h-3 bg-yellow-400 rounded-full animate-ping"></div>
                  </div>
                  <Bell v-else class="h-6 w-6" />
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-start justify-between gap-2 mb-2">
                    <h4 class="text-sm font-bold text-white tracking-tight">{{ notification.title }}</h4>
                    <button
                      @click.stop="deleteNotification(notification.id)"
                      class="p-1.5 text-gray-400 hover:text-red-400 hover:bg-red-500/10 transition-all rounded-lg opacity-0 group-hover:opacity-100 border border-transparent hover:border-red-500/30"
                      title="Delete"
                    >
                      <X class="h-3.5 w-3.5" />
                    </button>
                  </div>
                  <p class="text-xs text-gray-300 mb-3 line-clamp-2 leading-relaxed">{{ notification.message || 'No message' }}</p>
                  
                  <!-- Level Up Info -->
                  <div v-if="notification.type === 'level_up' && notification.level" class="mb-3">
                    <div class="flex items-center gap-3 p-3 rounded-lg bg-gradient-to-r from-purple-500/20 via-pink-500/10 to-purple-500/20 border border-purple-500/30 backdrop-blur-sm">
                      <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500/40 to-pink-500/30 flex items-center justify-center border border-purple-400/50 shadow-lg">
                          <span class="text-lg font-bold text-white">{{ notification.level }}</span>
                        </div>
                        <div>
                          <div class="text-xs font-bold text-purple-300">{{ notification.level_name || 'Level Up' }}</div>
                          <div class="text-xs text-purple-400/80">Niveau atteint!</div>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Gain/Loss Info -->
                  <div v-if="notification.gain_loss !== undefined && notification.gain_loss !== null && notification.type !== 'level_up'" class="flex items-center gap-2 flex-wrap mb-2">
                    <span
                      :class="[
                        'text-xs font-semibold px-2 py-0.5 rounded-md backdrop-blur-sm',
                        notification.type === 'profit' 
                          ? 'text-green-300 bg-green-500/20 border border-green-500/30' 
                          : 'text-red-300 bg-red-500/20 border border-red-500/30'
                      ]"
                    >
                      {{ notification.type === 'profit' ? '+' : '' }}{{ formatCurrency(notification.gain_loss) }}
                    </span>
                    <span
                      v-if="notification.gain_loss_percent !== undefined && notification.gain_loss_percent !== null"
                      :class="[
                        'text-xs px-2 py-0.5 rounded-md backdrop-blur-sm',
                        notification.type === 'profit' 
                          ? 'text-green-300 bg-green-500/10 border border-green-500/20' 
                          : 'text-red-300 bg-red-500/10 border border-red-500/20'
                      ]"
                    >
                      ({{ notification.type === 'profit' ? '+' : '' }}{{ formatPercent(notification.gain_loss_percent) }}%)
                    </span>
                  </div>

                  <div class="flex items-center gap-2 mt-2">
                    <span class="text-xs text-gray-500 font-medium">{{ formatTime(notification.created_at) }}</span>
                    <span v-if="notification.crypto_symbol && notification.type !== 'level_up'" class="text-xs px-2 py-0.5 rounded-md bg-white/5 text-gray-400 border border-white/10 font-mono">
                      {{ notification.crypto_symbol }}
                    </span>
                  </div>
                  </div>

                <!-- Unread Indicator avec animation -->
                <div v-if="!notification.is_read" class="relative flex-shrink-0 mt-1">
                  <div class="w-3 h-3 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full shadow-lg shadow-blue-500/50 animate-pulse"></div>
                  <div class="absolute inset-0 w-3 h-3 bg-blue-400 rounded-full animate-ping opacity-75"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer avec design amélioré -->
        <div v-if="pagination && pagination.last_page > 1" class="px-5 py-4 border-t border-white/10 bg-gradient-to-r from-gray-800/50 to-gray-900/50 backdrop-blur-sm flex items-center justify-between">
          <button
            @click="loadMore"
            :disabled="loading || pagination.current_page >= pagination.last_page"
            class="px-4 py-2 text-xs font-semibold text-blue-400 hover:text-blue-300 bg-blue-500/10 hover:bg-blue-500/20 border border-blue-500/30 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 backdrop-blur-sm disabled:hover:bg-blue-500/10"
          >
            {{ loading ? 'Loading...' : 'Load more' }}
          </button>
          <span class="text-xs text-gray-400 font-medium">
            Page {{ pagination.current_page }} / {{ pagination.last_page }}
          </span>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Bell, TrendingUp, TrendingDown, X } from 'lucide-vue-next';
import { 
  getNotifications, 
  getUnreadNotificationsCount, 
  markNotificationAsRead, 
  markAllNotificationsAsRead,
  deleteNotification as deleteNotificationApi 
} from '@/services/api';
import type { Notification, Paginated } from '@/types';
import { formatEUR } from '@/utils/formatEUR';

const isOpen = ref(false);
const loading = ref(false);
const notifications = ref<Notification[]>([]);
const unreadCount = ref(0);
const pagination = ref<Paginated<Notification> | null>(null);
const dropdownRef = ref<HTMLElement | null>(null);
const currentPage = ref(1);

function formatCurrency(value: number | string | null | undefined) {
  if (value === null || value === undefined) return formatEUR(0);
  const numValue = typeof value === 'string' ? parseFloat(value) : value;
  return formatEUR(isNaN(numValue) ? 0 : numValue);
}

function formatPercent(value: number | string | null | undefined): string {
  if (value === null || value === undefined) return '0.00';
  const numValue = typeof value === 'string' ? parseFloat(value) : value;
  if (isNaN(numValue)) return '0.00';
  return numValue.toFixed(2);
}

function formatTime(date: string) {
  const d = new Date(date);
  const now = new Date();
  const diff = now.getTime() - d.getTime();
  const minutes = Math.floor(diff / 60000);
  const hours = Math.floor(diff / 3600000);
  const days = Math.floor(diff / 86400000);

  if (minutes < 1) return 'Just now';
  if (minutes < 60) return `${minutes}m ago`;
  if (hours < 24) return `${hours}h ago`;
  if (days < 7) return `${days}d ago`;
  return d.toLocaleDateString();
}

async function loadNotifications(page: number = 1, append: boolean = false) {
  loading.value = true;
  try {
    const data = await getNotifications({ page, per_page: 10 });
    
    // Vérifier que data.data existe et est un tableau
    if (data && data.data && Array.isArray(data.data)) {
      if (append) {
        notifications.value = [...notifications.value, ...data.data];
      } else {
        notifications.value = data.data;
      }
      pagination.value = data;
      currentPage.value = page;
    } else {
      console.warn('Notifications data format incorrect:', data);
      if (!append) {
        notifications.value = [];
      }
    }
  } catch (e) {
    console.error('Error loading notifications:', e);
    if (!append) {
      notifications.value = [];
    }
  } finally {
    loading.value = false;
  }
}

async function loadUnreadCount() {
  try {
    const data = await getUnreadNotificationsCount();
    unreadCount.value = data.count;
  } catch (e) {
    console.error('Error loading unread count:', e);
  }
}

async function handleNotificationClick(notification: Notification) {
  if (!notification.is_read) {
    try {
      await markNotificationAsRead(notification.id);
      notification.is_read = true;
      notification.read_at = new Date().toISOString();
      if (unreadCount.value > 0) {
        unreadCount.value--;
      }
    } catch (e) {
      console.error('Error marking notification as read:', e);
    }
  }
}

async function markAllAsRead() {
  try {
    await markAllNotificationsAsRead();
    notifications.value.forEach(n => {
      n.is_read = true;
      n.read_at = new Date().toISOString();
    });
    unreadCount.value = 0;
  } catch (e) {
    console.error('Error marking all as read:', e);
  }
}

async function deleteNotification(id: number) {
  try {
    await deleteNotificationApi(id);
    notifications.value = notifications.value.filter(n => n.id !== id);
    if (notifications.value.find(n => n.id === id && !n.is_read)) {
      if (unreadCount.value > 0) {
        unreadCount.value--;
      }
    }
  } catch (e) {
    console.error('Error deleting notification:', e);
  }
}

function toggleDropdown() {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    // Toujours recharger les notifications quand on ouvre le dropdown
    loadNotifications(1);
    // Recharger aussi le compteur de non lues
    loadUnreadCount();
  }
}

function closeDropdown() {
  isOpen.value = false;
}

function loadMore() {
  if (pagination.value && currentPage.value < pagination.value.last_page) {
    loadNotifications(currentPage.value + 1, true);
  }
}

// Close dropdown when clicking outside
function handleClickOutside(event: MouseEvent) {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    closeDropdown();
  }
}

let refreshInterval: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
  loadUnreadCount();
  document.addEventListener('click', handleClickOutside);
  
  // Refresh unread count every 30 seconds
  refreshInterval = setInterval(() => {
    loadUnreadCount();
    // Si le dropdown est ouvert, recharger aussi les notifications
    if (isOpen.value) {
      loadNotifications(currentPage.value);
    }
  }, 30000);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  if (refreshInterval) {
    clearInterval(refreshInterval);
  }
});

// Expose unreadCount for parent component
defineExpose({
  unreadCount,
  loadUnreadCount
});
</script>

<style scoped>
/* Dropdown transition avec effet futuriste */
.dropdown-enter-active {
  transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1), 
              transform 0.3s cubic-bezier(0.4, 0, 0.2, 1),
              filter 0.3s ease;
}

.dropdown-leave-active {
  transition: opacity 0.2s cubic-bezier(0.4, 0, 0.2, 1), 
              transform 0.2s cubic-bezier(0.4, 0, 0.2, 1),
              filter 0.2s ease;
}

.dropdown-enter-from {
  opacity: 0;
  transform: translateY(-15px) scale(0.95) rotateX(5deg);
  filter: blur(4px);
}

.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px) scale(0.98);
  filter: blur(2px);
}

/* Bounce transition for badge avec effet amélioré */
.bounce-enter-active {
  animation: bounce-in 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.bounce-leave-active {
  animation: bounce-out 0.25s ease-in;
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

/* Line clamp pour le texte */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Effet de brillance sur les notifications non lues */
.group:not(.is-read)::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    90deg,
    transparent,
    rgba(255, 255, 255, 0.1),
    transparent
  );
  transition: left 0.5s ease;
}

.group:hover:not(.is-read)::before {
  left: 100%;
}

/* Animation pour les icônes */
.group:hover .w-12 {
  animation: icon-pulse 0.6s ease-in-out;
}

@keyframes icon-pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1) rotate(5deg);
  }
}

/* Scrollbar personnalisée pour le dropdown */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, rgba(59, 130, 246, 0.5), rgba(147, 51, 234, 0.5));
  border-radius: 3px;
  transition: background 0.3s ease;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, rgba(59, 130, 246, 0.7), rgba(147, 51, 234, 0.7));
}

/* Effet de glow sur les badges de gain/perte */
.text-green-300 {
  text-shadow: 0 0 8px rgba(34, 197, 94, 0.4);
}

.text-red-300 {
  text-shadow: 0 0 8px rgba(239, 68, 68, 0.4);
}

/* Animation pour l'indicateur non lu */
@keyframes pulse-glow {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
  }
  50% {
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0);
  }
}

.animate-pulse {
  animation: pulse-glow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>

