<template>
  <div class="min-h-screen bg-gray-900 text-white">
    <AdminSidebar v-model:is-mobile-open="isMobileMenuOpen" @close="closeMobileMenu" @toggle-mobile-menu="toggleMobileMenu" />
    
    <div class="ml-0 md:ml-64 min-h-screen transition-all duration-300">
      <main class="p-3 sm:p-6">
        <!-- Sidebar Toggle Button - Visible in header area -->
        <button
          @click="toggleMobileMenu"
          aria-label="Toggle sidebar"
          class="fixed top-4 left-4 z-[60] p-2.5 rounded-lg hover:bg-gray-700/50 active:bg-gray-700/70 transition-all touch-manipulation shadow-lg bg-gray-800/90 backdrop-blur-sm border border-gray-700/50 md:hidden"
          :class="{ 'left-[85vw] max-lg:left-[300px]': isMobileMenuOpen }"
        >
          <div class="relative w-5 h-5">
            <!-- Top Line -->
            <span
              class="absolute top-0 left-0 w-full h-0.5 bg-gray-300 rounded-full transition-all duration-300"
              :class="isMobileMenuOpen ? 'top-2 rotate-45' : ''"
            ></span>
            <!-- Middle Line -->
            <span
              class="absolute top-2 left-0 w-full h-0.5 bg-gray-300 rounded-full transition-all duration-200"
              :class="isMobileMenuOpen ? 'opacity-0' : 'opacity-100'"
            ></span>
            <!-- Bottom Line -->
            <span
              class="absolute top-4 left-0 w-full h-0.5 bg-gray-300 rounded-full transition-all duration-300"
              :class="isMobileMenuOpen ? 'top-2 -rotate-45' : ''"
            ></span>
          </div>
        </button>

        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AdminSidebar from '../components/AdminSidebar.vue';
import { RouterView } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const router = useRouter();
const isMobileMenuOpen = ref(false);

function toggleMobileMenu() {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
}

function closeMobileMenu() {
  isMobileMenuOpen.value = false;
}

onMounted(async () => {
  auth.hydrate?.();
  if (!auth.token) {
    router.push({ name: 'Signin' });
    return;
  }
  if (!auth.user) {
    await auth.fetchCurrentUser();
  }
  if (auth.user?.role !== 'admin') {
    router.push({ name: 'Signin' });
  }
});
</script>

<style scoped>
/* layout uses Tailwind utilities */
</style>