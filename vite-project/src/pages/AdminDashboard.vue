<template>
  <div class="min-h-screen bg-gray-900 text-white">
    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold">Dashboard</h1>
          <p class="text-gray-400 mt-1 text-sm sm:text-base">Welcome to your dashboard</p>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div v-for="(stat, i) in stats" :key="i" class="bg-gray-800 rounded-xl p-6 border border-gray-700 hover:border-gray-600 transition-all">
          <div class="flex items-center justify-between mb-4">
            <div class="text-gray-400">
              <component :is="stat.icon" class="h-6 w-6" />
            </div>
          </div>
          <div class="space-y-1">
            <div class="text-2xl font-bold">{{ stat.value }}</div>
            <div class="text-gray-400 text-sm">{{ stat.title }}</div>
          </div>
        </div>
      </div>

      <!-- Content Placeholder -->
      <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <router-link
            to="/trade"
            class="p-4 bg-gray-700 rounded-lg hover:bg-gray-600 transition-colors text-center"
          >
            Trade
          </router-link>
          <router-link
            to="/app/portfolio"
            class="p-4 bg-gray-700 rounded-lg hover:bg-gray-600 transition-colors text-center"
          >
            Portfolio
          </router-link>
          <router-link
            to="/profile"
            class="p-4 bg-gray-700 rounded-lg hover:bg-gray-600 transition-colors text-center"
          >
            Profile
          </router-link>
        </div>
      </div>
    </div>

    <!-- Footer - Full Width -->
    <FooterSection />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Wallet, TrendingUp, DollarSign, Activity } from 'lucide-vue-next';
import FooterSection from '../components/sectionsLanding/FooterSection.vue';

const stats = [
  { title: 'Balance', value: '€0.00', icon: Wallet },
  { title: 'Total Trades', value: '0', icon: TrendingUp },
  { title: 'Profit/Loss', value: '€0.00', icon: DollarSign },
  { title: 'Active Orders', value: '0', icon: Activity },
];
</script>

<style scoped>
/* Styles handled by Tailwind */
</style>

