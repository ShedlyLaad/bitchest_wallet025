<template>
  <div class="min-h-screen bg-gray-900 text-white">
    <AdminSidebar />
    <div class="ml-64 min-h-screen">
      <AdminTopbar />
      <main class="p-3 sm:p-6">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AdminSidebar from '../components/AdminSidebar.vue';
import AdminTopbar from '../components/AdminTopbar.vue';
import { RouterView } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const router = useRouter();

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