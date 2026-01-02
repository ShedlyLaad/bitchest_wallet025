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
        class="absolute right-0 mt-2 w-96 bg-white/5 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl z-50 max-h-[600px] flex flex-col overflow-hidden"
        @click.stop
      >
        <!-- Header -->
        <div class="px-4 py-3 border-b border-white/10 flex items-center justify-between bg-white/5">
          <h3 class="text-lg font-semibold text-white">Notifications</h3>
          <div class="flex items-center gap-2">
            <button
              v-if="unreadCount > 0"
              @click="markAllAsRead"
              class="text-xs text-blue-400 hover:text-blue-300 transition-colors"
            >
              Mark all read
            </button>
            <button
              @click="closeDropdown"
              class="p-1 text-gray-400 hover:text-white transition-colors rounded-lg hover:bg-white/10"
            >
              <X class="h-4 w-4" />
            </button>
          </div>
        </div>

        <!-- Notifications List -->
        <div class="overflow-y-auto flex-1">
          <div v-if="loading && notifications.length === 0" class="p-8 text-center">
            <div class="h-8 w-8 border-2 border-blue-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
            <p class="text-gray-400 text-sm mt-4">Loading notifications...</p>
          </div>
          
          <div v-else-if="notifications.length === 0" class="p-8 text-center">
            <Bell class="h-12 w-12 text-gray-500 mx-auto mb-3 opacity-50" />
            <p class="text-gray-400 text-sm">No notifications</p>
          </div>

          <div v-else class="divide-y divide-white/5">
            <div
              v-for="notification in notifications"
              :key="notification.id"
              @click="handleNotificationClick(notification)"
              :class="[
                'p-4 hover:bg-white/5 transition-all cursor-pointer border-l-4',
                notification.is_read 
                  ? 'border-transparent bg-white/2' 
                  : notification.type === 'profit'
                    ? 'border-green-500/50 bg-green-500/5'
                    : notification.type === 'loss'
                      ? 'border-red-500/50 bg-red-500/5'
                      : 'border-blue-500/50 bg-blue-500/5'
              ]"
            >
              <div class="flex items-start gap-3">
                <!-- Icon -->
                <div
                  :class="[
                    'w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0',
                    notification.type === 'profit'
                      ? 'bg-green-500/20 text-green-400'
                      : notification.type === 'loss'
                        ? 'bg-red-500/20 text-red-400'
                        : 'bg-blue-500/20 text-blue-400'
                  ]"
                >
                  <TrendingUp v-if="notification.type === 'profit'" class="h-5 w-5" />
                  <TrendingDown v-else-if="notification.type === 'loss'" class="h-5 w-5" />
                  <Bell v-else class="h-5 w-5" />
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-start justify-between gap-2 mb-1">
                    <h4 class="text-sm font-semibold text-white">{{ notification.title }}</h4>
                    <button
                      @click.stop="deleteNotification(notification.id)"
                      class="p-1 text-gray-400 hover:text-red-400 transition-colors opacity-0 group-hover:opacity-100"
                      title="Delete"
                    >
                      <X class="h-3 w-3" />
                    </button>
                  </div>
                  <p class="text-xs text-gray-300 mb-2 line-clamp-2">{{ notification.message }}</p>
                  
                  <!-- Gain/Loss Info -->
                  <div v-if="notification.gain_loss !== undefined" class="flex items-center gap-2">
                    <span
                      :class="[
                        'text-xs font-semibold',
                        notification.type === 'profit' ? 'text-green-400' : 'text-red-400'
                      ]"
                    >
                      {{ notification.type === 'profit' ? '+' : '' }}{{ formatCurrency(notification.gain_loss) }}
                    </span>
                    <span
                      v-if="notification.gain_loss_percent !== undefined"
                      :class="[
                        'text-xs',
                        notification.type === 'profit' ? 'text-green-400' : 'text-red-400'
                      ]"
                    >
                      ({{ notification.type === 'profit' ? '+' : '' }}{{ notification.gain_loss_percent.toFixed(2) }}%)
                    </span>
                  </div>

                  <div class="text-xs text-gray-500 mt-2">
                    {{ formatTime(notification.created_at) }}
                  </div>
                </div>

                <!-- Unread Indicator -->
                <div v-if="!notification.is_read" class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 mt-1"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div v-if="pagination && pagination.last_page > 1" class="px-4 py-3 border-t border-white/10 bg-white/5 flex items-center justify-between">
          <button
            @click="loadMore"
            :disabled="loading || pagination.current_page >= pagination.last_page"
            class="text-xs text-blue-400 hover:text-blue-300 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            Load more
          </button>
          <span class="text-xs text-gray-400">
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

function formatCurrency(value: number) {
  return formatEUR(value);
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
    if (append) {
      notifications.value = [...notifications.value, ...data.data];
    } else {
      notifications.value = data.data;
    }
    pagination.value = data;
    currentPage.value = page;
  } catch (e) {
    console.error('Error loading notifications:', e);
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
  if (isOpen.value && notifications.value.length === 0) {
    loadNotifications(1);
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
/* Dropdown transition */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px) scale(0.95);
}

/* Bounce transition for badge */
.bounce-enter-active {
  animation: bounce-in 0.3s ease;
}

.bounce-leave-active {
  animation: bounce-out 0.2s ease;
}

@keyframes bounce-in {
  0% {
    transform: scale(0);
  }
  50% {
    transform: scale(1.2);
  }
  100% {
    transform: scale(1);
  }
}

@keyframes bounce-out {
  0% {
    transform: scale(1);
  }
  100% {
    transform: scale(0);
  }
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>

