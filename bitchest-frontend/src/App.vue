<template>
  <div class="min-h-screen bg-gray-900">
    <Navbar v-if="!currentRoute.meta?.hideNavbar" />
    <router-view />
  </div>
</template>

<script setup lang="ts">
import { useRoute } from 'vue-router';
import Navbar from './components/Navbar.vue';

const currentRoute = useRoute();
</script>

<style scoped>
/* nothing scoped here — global styles in index.css */
</style>