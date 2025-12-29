<template>
  <div>
    <!-- Intro screen shown once at app start -->
    <IntroWrapper v-if="!introComplete" @end="handleIntroEnd" />

    <!-- Main app (routes + optional Navbar) -->
    <div v-else class="min-h-screen bg-gray-900">
      <Navbar v-if="!currentRoute.meta?.hideNavbar" />
      <router-view />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import Navbar from './components/Navbar.vue';
import IntroWrapper from './pages/IntroWrapper.vue';

const introComplete = ref(false);
const currentRoute = useRoute();

function handleIntroEnd() {
  introComplete.value = true;
}
</script>

<style scoped>
/* nothing scoped here — global styles in index.css */
</style>