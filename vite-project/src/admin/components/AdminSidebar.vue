<template>
  <aside class="fixed left-0 top-0 h-screen w-64 bg-gray-800 border-r border-gray-700 z-40 overflow-y-auto">
    <div class="flex flex-col h-full">
      <!-- Sidebar Header -->
      <div class="p-4 flex items-center justify-center">
        <img :src="AdminLogo" alt="Admin Logo" class="h-auto w-full max-w-[120px] object-contain" />
      </div>

      <!-- Navigation Items -->
      <nav class="flex-1 p-4 space-y-2">
        <RouterLink
          v-for="item in sidebarItems"
          :key="item.path"
          :to="item.path"
          class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors"
          :class="isActive(item.path) ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700/50 hover:text-white'"
        >
          <span :style="isActive(item.path) ? { color: 'var(--blue)' } : {}">
            <component :is="item.icon" class="h-5 w-5" />
          </span>
          <span class="font-medium">{{ item.label }}</span>
        </RouterLink>
      </nav>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRoute, RouterLink } from 'vue-router';
import { LayoutDashboard, Users, TrendingUp, FileText } from 'lucide-vue-next';
import AdminLogo from '../../assets/ADMIN.png';

interface SidebarItem {
  path: string;
  label: string;
  icon: any;
}

const route = useRoute();

const sidebarItems: SidebarItem[] = [
  { path: '/admin', label: 'Dashboard', icon: LayoutDashboard },
  { path: '/admin/users', label: 'Users', icon: Users },
  { path: '/admin/market', label: 'Market', icon: TrendingUp },
  { path: '/admin/transactions', label: 'Transactions', icon: FileText },
];

function isActive(path: string) {
  if (path === '/admin') {
    return route.path === '/admin';
  }
  return route.path.startsWith(path);
}
</script>

<style scoped>
/* Sidebar styling is handled by Tailwind classes */
</style>