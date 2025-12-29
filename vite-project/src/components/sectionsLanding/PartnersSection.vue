<template>
  <ScrollReveal width="100%">
    <section class="relative py-16 md:py-24 overflow-hidden">
      <!-- Background Effects -->
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 rounded-full blur-3xl animate-pulse delay-1000" :style="{ backgroundColor: 'var(--blue-dark)', opacity: 0.1 }"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-green-500/5 rounded-full blur-3xl animate-pulse delay-2000"></div>
      </div>

      <div class="relative max-w-[90vw] mx-auto">
        <div class="text-center mb-8 md:mb-12">
          <h2 class="text-4xl font-bold text-white inline-block bg-clip-text text-transparent" :style="{ background: 'linear-gradient(to right, var(--blue), var(--blue-dark), var(--accent-green))', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent' }">
            Our Trusted Partners
          </h2>
          <div class="mt-4 text-gray-400">Powering the future of digital assets together</div>
        </div>

        <div class="relative">
          <div class="flex items-stretch space-x-4 md:space-x-6 overflow-x-auto scrollbar-hide py-4 px-4 md:px-20">
            <div
              v-for="(p, index) in partners"
              :key="index"
              class="flex-none w-80 group relative"
            >
              <div
                class="relative h-full rounded-2xl border border-white/10 bg-gradient-to-br from-gray-800/50 via-gray-800/30 to-gray-800/50 backdrop-blur-xl p-6 transition-all duration-300 hover:border-blue-500/50 hover:shadow-2xl hover:shadow-blue-500/10 flex flex-col justify-between"
              >
                <!-- Animated Background -->
                <div class="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                  <div class="absolute inset-0 bg-blue-500/10 blur-3xl"></div>
                </div>

                <!-- Content -->
                <div class="relative z-10">
                  <!-- Header -->
                  <div class="flex items-start justify-between mb-6">
                    <div class="p-3 bg-white/5 rounded-xl border border-white/10">
                      <img :src="p.icon" :alt="p.name" class="w-8 h-8" />
                    </div>
                    <div class="text-right">
                      <div class="text-2xl font-bold text-white">{{ p.value }}</div>
                      <div class="text-green-400 text-sm">{{ p.growth }}</div>
                    </div>
                  </div>

                  <!-- Info -->
                  <h3 class="text-xl font-bold text-white mb-2">{{ p.name }}</h3>
                  <p class="text-gray-400 text-sm">{{ p.description }}</p>
                </div>

                <!-- Hover Effects -->
                <div class="relative z-10 mt-6 flex items-center justify-between text-sm">
                  <span class="text-gray-500">Partnership since 2021</span>
                  <div class="opacity-0 group-hover:opacity-100 transition-opacity bg-blue-500/10 px-3 py-1 rounded-full text-blue-400">
                    View Details →
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </ScrollReveal>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import ScrollReveal from '../ScrollReveal.vue';
import { partners } from '../../data/mockData';

// (partners is expected to be an array of { name, icon, value, growth, description })
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>