<template>
  <footer class="bg-gray-800/30 border-t border-gray-700/50 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Logo and Company Info -->
        <div class="flex items-center gap-4">
          <img :src="BitchestFooterLogo" alt="Bitchest Logo" class="h-12 w-auto" />
          <div class="hidden sm:block h-6 w-px bg-gray-700"></div>
          <div class="hidden sm:block text-sm text-gray-400">
            <p class="font-medium text-gray-300">Soluyman, 108 Av. République</p>
            <p class="text-xs mt-0.5">© {{ year }} Bitchest. All rights reserved.</p>
          </div>
        </div>

        <!-- Contact and Links -->
        <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6 text-sm">
          <div class="flex items-center gap-2 text-gray-400">
            <Mail class="h-4 w-4" />
            <a href="mailto:admin@bitchest.com" class="hover:text-white transition-colors">
              admin@bitchest.com
            </a>
          </div>
          <div class="flex items-center gap-2 text-gray-400">
            <Phone class="h-4 w-4" />
            <a href="tel:+21672290512" class="hover:text-white transition-colors">
              +216 72 290 512
            </a>
          </div>
          <div class="flex items-center gap-3">
            <a
              v-for="social in socialLinks"
              :key="social.name"
              :href="social.url"
              class="p-2 bg-gray-700/50 rounded-lg hover:bg-gray-700 transition-colors group"
              target="_blank"
              rel="noopener noreferrer"
              :aria-label="`Follow on ${social.name}`"
            >
              <component :is="social.icon" class="h-4 w-4 text-gray-400 group-hover:text-blue-400 transition-colors" />
            </a>
          </div>
        </div>
      </div>

      <!-- Mobile: Address and Copyright -->
      <div class="sm:hidden mt-4 pt-4 border-t border-gray-700/50 text-center">
        <p class="text-xs text-gray-400">Soluyman, 108 Av. République</p>
        <p class="text-xs text-gray-500 mt-1">© {{ year }} Bitchest. All rights reserved.</p>
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
  Github
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

