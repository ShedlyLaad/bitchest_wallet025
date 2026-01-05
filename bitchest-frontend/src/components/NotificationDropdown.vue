<template>
  <div class="relative" ref="dropdownRef">
    <button 
      @click.stop.prevent="toggleDropdown"
      @mousedown.prevent
      class="relative p-2 rounded-lg text-white hover:bg-white/10 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500 select-none"
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
        class="absolute right-0 mt-2 w-[380px] bg-gradient-to-br from-gray-900/95 via-gray-800/95 to-gray-900/95 backdrop-blur-2xl rounded-xl border border-white/20 shadow-2xl z-50 max-h-[550px] flex flex-col overflow-hidden ring-1 ring-white/10 select-none"
        @click.stop.prevent
      >
        <!-- Header avec effet glassmorphism -->
        <div class="px-4 py-3 border-b border-white/10 flex items-center justify-between bg-gradient-to-r from-blue-500/10 via-purple-500/5 to-blue-500/10 backdrop-blur-sm">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-blue-500/20 to-purple-500/20 border border-white/20 flex items-center justify-center backdrop-blur-sm">
              <Bell class="h-4 w-4 text-blue-400" />
            </div>
            <div>
              <h3 class="text-base font-bold text-white tracking-tight">Notifications</h3>
              <p v-if="unreadCount > 0" class="text-xs text-blue-400 font-medium">{{ unreadCount }} unread</p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <button
              v-if="unreadCount > 0"
              @click.stop.prevent="markAllAsRead"
              @mousedown.prevent
              :disabled="loading"
              class="relative px-2 py-1 text-xs font-medium text-blue-400 hover:text-blue-300 bg-blue-500/10 hover:bg-blue-500/20 border border-blue-500/30 rounded-md transition-all duration-150 backdrop-blur-sm active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed group select-none"
            >
              <span class="flex items-center gap-1">
                <svg v-if="!loading" class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <svg v-else class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="hidden sm:inline">{{ loading ? '...' : 'Read all' }}</span>
              </span>
            </button>
            <button
              @click.stop.prevent="closeDropdown"
              @mousedown.prevent
              class="relative p-2 text-gray-400 hover:text-white transition-all duration-150 rounded-lg hover:bg-white/10 active:scale-90 border border-transparent hover:border-white/20 group overflow-hidden select-none"
            >
              <X class="h-4 w-4 relative z-10 transition-transform duration-200 group-hover:rotate-90" />
              <span class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-200 rounded-lg"></span>
            </button>
          </div>
        </div>

        <!-- Notifications List -->
        <div class="overflow-y-auto flex-1 max-h-[500px]">
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
              v-for="notification in displayedNotifications"
              :key="notification.id"
              @click.stop.prevent="handleNotificationClick(notification)"
              @mousedown.prevent
              :class="[
                'group relative p-3 hover:bg-white/5 transition-all duration-150 cursor-pointer border-l-3 backdrop-blur-sm select-none',
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
              
              <div class="relative flex items-start gap-3">
                <!-- Level Up Medal (inspired by image) - Positionné en premier -->
                <div v-if="notification.type === 'level_up' && notification.level" class="flex-shrink-0 relative">
                  <div class="relative w-14 h-14 flex items-center justify-center">
                    <!-- Medal Circle -->
                    <div class="absolute inset-0 rounded-full bg-gradient-to-br from-yellow-400 via-orange-400 to-yellow-500 shadow-lg border-2 border-yellow-300/50 flex items-center justify-center">
                      <span class="text-xl font-bold text-white drop-shadow-lg">⭐</span>
                    </div>
                    <!-- Ribbon -->
                    <div class="absolute -bottom-0.5 left-1/2 transform -translate-x-1/2 w-7 h-2.5 bg-gradient-to-b from-orange-500 to-orange-600 rounded-b-sm shadow-md"></div>
                    <!-- Sparkles -->
                    <div class="absolute -top-0.5 -right-0.5 w-1.5 h-1.5 bg-yellow-300 rounded-full animate-ping"></div>
                    <div class="absolute -bottom-0.5 -left-0.5 w-1 h-1 bg-yellow-400 rounded-full animate-pulse"></div>
                    <div class="absolute top-1.5 -left-1.5 w-0.5 h-0.5 bg-orange-300 rounded-full"></div>
                  </div>
                </div>

                <!-- Icon avec effet glassmorphism -->
                <div
                  v-else
                  :class="[
                    'w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 border backdrop-blur-sm transition-all duration-200 group-hover:scale-105 overflow-hidden',
                    notification.type === 'profit'
                      ? 'bg-gradient-to-br from-green-500/30 to-green-600/20 text-green-400 border-green-500/40 shadow-md shadow-green-500/20'
                      : notification.type === 'loss'
                        ? 'bg-gradient-to-br from-red-500/30 to-red-600/20 text-red-400 border-red-500/40 shadow-md shadow-red-500/20'
                        : 'bg-gradient-to-br from-blue-500/30 to-blue-600/20 text-blue-400 border-blue-500/40 shadow-md shadow-blue-500/20'
                  ]"
                >
                  <!-- Crypto Logo -->
                  <img 
                    v-if="notification.crypto_symbol && getCryptoIcon(notification.crypto_symbol)"
                    :src="getCryptoIcon(notification.crypto_symbol)"
                    :alt="notification.crypto_symbol"
                    class="w-full h-full object-cover"
                    @error="(e) => { (e.target as HTMLImageElement).style.display = 'none'; }"
                  />
                  <TrendingUp v-else-if="notification.type === 'profit'" class="h-5 w-5" />
                  <TrendingDown v-else-if="notification.type === 'loss'" class="h-5 w-5" />
                  <Bell v-else class="h-5 w-5" />
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-start justify-between gap-2 mb-1">
                    <h4 class="text-xs font-bold text-white tracking-tight leading-tight">{{ notification.title }}</h4>
                    <button
                      @click.stop.prevent="deleteNotification(notification.id)"
                      @mousedown.prevent
                      class="relative p-1 text-gray-400 hover:text-red-400 hover:bg-red-500/10 transition-all duration-150 rounded opacity-0 group-hover:opacity-100 active:scale-90 select-none"
                      title="Delete"
                    >
                      <X class="h-3 w-3 relative z-10 transition-transform duration-200 group-hover:rotate-90" />
                    </button>
                  </div>
                  <p class="text-xs text-gray-300 mb-2 line-clamp-2 leading-snug">{{ notification.message || 'No message' }}</p>
                  
                  <!-- Level Up Info Compact -->
                  <div v-if="notification.type === 'level_up' && notification.level" class="mb-2">
                    <div class="flex items-center gap-2">
                      <div class="px-2 py-1 rounded-md bg-gradient-to-r from-purple-500/20 to-pink-500/20 border border-purple-500/30">
                        <span class="text-xs font-bold text-purple-300">Level {{ notification.level }}</span>
                      </div>
                      <span class="text-xs text-purple-400/80">{{ notification.level_name || 'Level Up' }}</span>
                    </div>
                  </div>
                  
                  <!-- Gain/Loss Info -->
                  <div v-if="notification.gain_loss !== undefined && notification.gain_loss !== null && notification.type !== 'level_up'" class="flex items-center gap-1.5 flex-wrap mb-1.5">
                    <span
                      :class="[
                        'text-xs font-semibold px-1.5 py-0.5 rounded backdrop-blur-sm',
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
                        'text-xs px-1.5 py-0.5 rounded backdrop-blur-sm',
                        notification.type === 'profit' 
                          ? 'text-green-300 bg-green-500/10 border border-green-500/20' 
                          : 'text-red-300 bg-red-500/10 border border-red-500/20'
                      ]"
                    >
                      ({{ notification.type === 'profit' ? '+' : '' }}{{ formatPercent(notification.gain_loss_percent) }}%)
                    </span>
                  </div>

                  <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500">{{ formatTime(notification.created_at) }}</span>
                    <span v-if="notification.crypto_symbol && notification.type !== 'level_up'" class="text-xs px-1.5 py-0.5 rounded bg-white/5 text-gray-400 border border-white/10 font-mono">
                      {{ notification.crypto_symbol }}
                    </span>
                  </div>
                </div>

                <!-- Unread Indicator compact -->
                <div v-if="!notification.is_read" class="relative flex-shrink-0 mt-0.5">
                  <div class="w-2 h-2 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full shadow-md shadow-blue-500/50 animate-pulse"></div>
                </div>
              </div>
            </div>
            
            <!-- Indicateur de notifications supplémentaires -->
            <button
              v-if="remainingNotificationsCount > 0"
              @click.stop.prevent="loadMore"
              @mousedown.prevent
              :disabled="loading || !!(pagination && pagination.current_page >= pagination.last_page)"
              class="w-full p-2 text-center bg-gradient-to-r from-blue-500/10 via-purple-500/5 to-blue-500/10 border-t border-white/10 hover:from-blue-500/15 hover:via-purple-500/10 hover:to-blue-500/15 transition-all duration-150 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed group select-none"
            >
              <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-blue-500/20 border border-blue-500/30 backdrop-blur-sm group-hover:bg-blue-500/30 group-hover:border-blue-500/50 group-hover:shadow-sm transition-all duration-150">
                <span class="text-xs font-medium text-blue-300">+{{ remainingNotificationsCount }}</span>
                <span class="text-xs text-blue-400">
                  {{ loading ? '...' : 'more' }}
                </span>
                <svg v-if="!loading" class="h-2.5 w-2.5 text-blue-400 group-hover:translate-x-0.5 transition-transform duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <svg v-else class="animate-spin h-2.5 w-2.5 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </div>
            </button>
          </div>
        </div>

        <!-- Footer avec design amélioré -->
        <div v-if="pagination && pagination.last_page > 1 && remainingNotificationsCount === 0" class="px-4 py-2.5 border-t border-white/10 bg-gradient-to-r from-gray-800/50 to-gray-900/50 backdrop-blur-sm flex items-center justify-between">
          <button
            @click.stop.prevent="loadMore"
            @mousedown.prevent
            :disabled="loading || pagination.current_page >= pagination.last_page"
            class="relative px-3 py-1.5 text-xs font-medium text-blue-400 hover:text-blue-300 bg-blue-500/10 hover:bg-blue-500/20 border border-blue-500/30 rounded-md disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-150 backdrop-blur-sm disabled:hover:bg-blue-500/10 active:scale-95 group select-none"
          >
            <span class="flex items-center gap-1.5">
              <span>{{ loading ? '...' : 'More' }}</span>
              <svg v-if="!loading" class="h-2.5 w-2.5 group-hover:translate-x-0.5 transition-transform duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
              <svg v-else class="animate-spin h-2.5 w-2.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
          </button>
          <span class="text-xs text-gray-500">
            {{ pagination.current_page }}/{{ pagination.last_page }}
          </span>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
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
import { getCryptoIcon } from '@/utils/cryptoIcons';

const MAX_DISPLAYED_NOTIFICATIONS = 5; // Maximum de notifications affichées

const isOpen = ref(false);
const loading = ref(false);
const notifications = ref<Notification[]>([]);
const unreadCount = ref(0);
const pagination = ref<Paginated<Notification> | null>(null);
const dropdownRef = ref<HTMLElement | null>(null);
const currentPage = ref(1);

// Notifications affichées (limitées au maximum)
const displayedNotifications = computed(() => {
  return notifications.value.slice(0, MAX_DISPLAYED_NOTIFICATIONS);
});

// Nombre de notifications supplémentaires
const remainingNotificationsCount = computed(() => {
  const remaining = notifications.value.length - MAX_DISPLAYED_NOTIFICATIONS;
  return remaining > 0 ? remaining : 0;
});

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

let isProcessingClick = false;

async function handleNotificationClick(notification: Notification) {
  if (isProcessingClick) return;
  if (!notification.is_read) {
    isProcessingClick = true;
    try {
      await markNotificationAsRead(notification.id);
      notification.is_read = true;
      notification.read_at = new Date().toISOString();
      if (unreadCount.value > 0) {
        unreadCount.value--;
      }
    } catch (e) {
      console.error('Error marking notification as read:', e);
    } finally {
      setTimeout(() => {
        isProcessingClick = false;
      }, 300);
    }
  }
}

let isMarkingAllAsRead = false;

async function markAllAsRead() {
  if (loading.value || isMarkingAllAsRead) return;
  isMarkingAllAsRead = true;
  loading.value = true;
  try {
    await markAllNotificationsAsRead();
    notifications.value.forEach(n => {
      n.is_read = true;
      n.read_at = new Date().toISOString();
    });
    unreadCount.value = 0;
  } catch (e) {
    console.error('Error marking all as read:', e);
  } finally {
    loading.value = false;
    setTimeout(() => {
      isMarkingAllAsRead = false;
    }, 500);
  }
}

let isDeleting = false;

async function deleteNotification(id: number) {
  if (isDeleting) return;
  isDeleting = true;
  
  // Optimistic update - remove immediately for better UX
  const notificationToDelete = notifications.value.find(n => n.id === id);
  const wasUnread = notificationToDelete && !notificationToDelete.is_read;
  
  notifications.value = notifications.value.filter(n => n.id !== id);
  if (wasUnread && unreadCount.value > 0) {
    unreadCount.value--;
  }
  
  try {
    await deleteNotificationApi(id);
  } catch (e) {
    console.error('Error deleting notification:', e);
    // Re-add the notification if deletion failed
    if (notificationToDelete) {
      notifications.value.push(notificationToDelete);
      notifications.value.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
      if (wasUnread) {
        unreadCount.value++;
      }
    }
  } finally {
    setTimeout(() => {
      isDeleting = false;
    }, 300);
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

async function loadMore() {
  if (loading.value) return;
  if (!pagination.value || currentPage.value >= pagination.value.last_page) return;
  
  loading.value = true;
  try {
    await loadNotifications(currentPage.value + 1, true);
  } catch (e) {
    console.error('Error loading more notifications:', e);
  } finally {
    loading.value = false;
  }
}

// Close dropdown when clicking outside
function handleClickOutside(event: MouseEvent) {
  if (!isOpen.value) return;
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    // Vérifier que le clic n'est pas sur un élément interactif parent
    const target = event.target as HTMLElement;
    if (target.closest('[data-notification-ignore]')) {
      return;
    }
    closeDropdown();
  }
}

let refreshInterval: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
  loadUnreadCount();
  // Utiliser capture phase pour gérer les clics avant qu'ils ne se propagent
  document.addEventListener('click', handleClickOutside, true);
  
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
  document.removeEventListener('click', handleClickOutside, true);
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
/* Empêcher la sélection de texte pour une meilleure UX */
* {
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  -webkit-tap-highlight-color: transparent;
}

/* Permettre la sélection pour le texte informatif uniquement */
p, span.text-gray-300, span.text-gray-400, span.text-gray-500 {
  -webkit-user-select: text;
  -moz-user-select: text;
  -ms-user-select: text;
  user-select: text;
}

/* Améliorer les performances avec GPU acceleration */
.group {
  transform: translateZ(0);
  backface-visibility: hidden;
  perspective: 1000px;
}

/* Dropdown transition avec effet futuriste - optimisé pour fluidité */
.dropdown-enter-active {
  transition: opacity 0.2s cubic-bezier(0.4, 0, 0.2, 1), 
              transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  will-change: opacity, transform;
}

.dropdown-leave-active {
  transition: opacity 0.15s cubic-bezier(0.4, 0, 0.2, 1), 
              transform 0.15s cubic-bezier(0.4, 0, 0.2, 1);
  will-change: opacity, transform;
}

.dropdown-enter-from {
  opacity: 0;
  transform: translateY(-10px) scale(0.98);
}

.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-5px) scale(0.99);
}

/* Bounce transition for badge avec effet amélioré - optimisé */
.bounce-enter-active {
  animation: bounce-in 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
  will-change: transform, opacity;
}

.bounce-leave-active {
  animation: bounce-out 0.2s ease-in;
  will-change: transform, opacity;
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
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Effet de brillance sur les notifications non lues - optimisé */
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
    rgba(255, 255, 255, 0.08),
    transparent
  );
  transition: left 0.4s ease;
  will-change: left;
  pointer-events: none;
}

.group:hover:not(.is-read)::before {
  left: 100%;
}

/* Animation pour les icônes - optimisé */
.group:hover .w-10 {
  animation: icon-pulse 0.4s ease-in-out;
  will-change: transform;
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

