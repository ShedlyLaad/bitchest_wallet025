<template>
  <div class="min-h-screen bg-gray-900 text-white">
    <AdminSidebar v-model:is-mobile-open="isMobileMenuOpen" @close="closeMobileMenu" @toggle-mobile-menu="toggleMobileMenu" />
    <div class="ml-0 md:ml-64 min-h-screen transition-all duration-300">
      <main class="p-3 sm:p-6">
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