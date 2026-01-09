<template>
  <footer class="relative bg-gradient-to-br from-gray-900 via-gray-900 to-gray-950 border-t border-gray-700/50 mt-auto overflow-hidden shadow-2xl">
    <!-- Shadow effects -->
    <div class="absolute inset-0 pointer-events-none">
      <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-black/30 to-transparent"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex flex-col md:flex-row items-center justify-between gap-6">
        <!-- Enhanced Logo and Company Info -->
        <div class="flex items-center gap-4 group">
          <div class="relative">
            <div class="absolute inset-0 bg-black/20 rounded-xl blur-lg group-hover:bg-black/30 transition-all duration-300"></div>
            <img :src="BitchestFooterLogo" alt="Bitchest Logo" class="relative h-10 w-auto transition-transform duration-300 group-hover:scale-110 drop-shadow-lg" />
          </div>
          <div class="hidden sm:block h-8 w-px bg-gradient-to-b from-transparent via-gray-700 to-transparent"></div>
          <div class="hidden sm:block text-sm">
            <p class="font-medium text-gray-300 flex items-center gap-2">
              <MapPin class="h-3.5 w-3.5 text-gray-500" />
              Soluyman, 108 Av. République
            </p>
            <p class="text-xs mt-1.5 text-gray-400">
              © {{ year }} Bitchest. All rights reserved.
            </p>
          </div>
        </div>

        <!-- Enhanced Contact and Links -->
        <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6 text-sm">
          <!-- Email -->
          <div class="group flex items-center gap-2 px-4 py-2 bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl border border-gray-700/50 hover:border-gray-600/50 hover:shadow-2xl hover:shadow-black/50 transition-all duration-300">
            <div class="p-1.5 bg-gray-700/50 rounded-lg group-hover:bg-gray-700/60 transition-colors shadow-md">
              <Mail class="h-4 w-4 text-gray-400 group-hover:text-gray-300 group-hover:scale-110 transition-all" />
            </div>
            <a href="mailto:admin@bitchest.com" class="text-gray-300 hover:text-white font-medium transition-colors">
              admin@bitchest.com
            </a>
          </div>

          <!-- Phone -->
          <div class="group flex items-center gap-2 px-4 py-2 bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl border border-gray-700/50 hover:border-gray-600/50 hover:shadow-2xl hover:shadow-black/50 transition-all duration-300">
            <div class="p-1.5 bg-gray-700/50 rounded-lg group-hover:bg-gray-700/60 transition-colors shadow-md">
              <Phone class="h-4 w-4 text-gray-400 group-hover:text-gray-300 group-hover:scale-110 transition-all" />
            </div>
            <a href="tel:+21672290512" class="text-gray-300 hover:text-white font-medium transition-colors">
              +216 72 290 512
            </a>
          </div>

          <!-- Social Links -->
          <div class="flex items-center gap-3">
            <a
              v-for="social in socialLinks"
              :key="social.name"
              :href="social.url"
              class="group relative p-3 bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl border border-gray-700/50 hover:border-gray-600/50 hover:shadow-2xl hover:shadow-black/50 transition-all duration-300 hover:scale-110 hover:-translate-y-0.5"
              target="_blank"
              rel="noopener noreferrer"
              :aria-label="`Follow on ${social.name}`"
            >
              <div class="absolute inset-0 bg-gradient-to-r from-black/0 to-black/0 group-hover:from-black/10 group-hover:to-black/10 rounded-xl transition-all duration-300"></div>
              <component :is="social.icon" class="relative h-5 w-5 text-gray-400 group-hover:text-gray-300 transition-colors drop-shadow-md" />
            </a>
          </div>
        </div>
      </div>

      <!-- Enhanced Mobile: Address and Copyright -->
      <div class="sm:hidden mt-6 pt-6 border-t border-gray-700/50 text-center space-y-2">
        <p class="text-xs text-gray-400 flex items-center justify-center gap-2">
          <MapPin class="h-3 w-3 text-gray-500" />
          Soluyman, 108 Av. République
        </p>
        <p class="text-xs text-gray-500">
          © {{ year }} Bitchest. All rights reserved.
        </p>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
import {
  Mail,
  Phone,
  Facebook,
  Instagram,
  Linkedin,
  Github,
  MapPin
} from 'lucide-vue-next';

import BitchestFooterLogo from '@/assets/bitchest_Footer.png';

// Social links with specific URLs
const socialLinks = [
  { name: 'GitHub', icon: Github, url: 'https://github.com/ShedlyLaad' },
  { name: 'Instagram', icon: Instagram, url: 'https://www.instagram.com/shedlylaadhiby/' },
  { name: 'Facebook', icon: Facebook, url: 'https://www.facebook.com/TheRealShedly93' },
  { name: 'LinkedIn', icon: Linkedin, url: 'https://www.linkedin.com/in/chedlylaadhiby98/' }
];

const year = new Date().getFullYear();
</script>

<style scoped>
/* Minimal footer styling - relies on Tailwind utilities */
</style>

