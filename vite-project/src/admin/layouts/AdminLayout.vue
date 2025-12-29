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
import AdminSidebar from '../components/AdminSidebar.vue';
import AdminTopbar from '../components/AdminTopbar.vue';
import { RouterView } from 'vue-router';
</script>

<style scoped>
/* layout uses Tailwind utilities */
</style>