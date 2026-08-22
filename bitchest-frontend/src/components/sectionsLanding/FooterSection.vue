<template>
  <ScrollReveal width="100%">
    <footer class="relative overflow-hidden bg-gray-900">
      <SectionBackground :particles="false" :orbs="[]" />

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 z-10">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 py-12 md:py-16">
          <!-- Company Info -->
          <div class="space-y-6">
            <div class="flex items-center">
              <img :src="BitchestFooterLogo" alt="Bitchest Logo" class="h-15 w-auto" />
            </div>
            <p class="text-gray-400 text-sm">
              The world's leading cryptocurrency trading platform. Trusted by millions of users worldwide.
            </p>

            <div class="space-y-2">
              <div class="flex items-center space-x-2 text-gray-400">
                <Shield class="h-4 w-4" />
                <span class="text-sm">Bank-grade Security</span>
              </div>
              <div class="flex items-center space-x-2 text-gray-400">
                <CheckCircle class="h-4 w-4" />
                <span class="text-sm">Licensed &amp; Regulated</span>
              </div>
            </div>
          </div>

          <!-- Quick Links -->
          <div class="space-y-6">
            <h3 class="text-lg font-semibold">Quick Links</h3>
            <div class="grid grid-cols-1 gap-3">
              <RouterLink to="/trade" class="text-gray-400 hover:text-white transition-colors">Trade</RouterLink>
              <RouterLink to="/support" class="text-gray-400 hover:text-white transition-colors">Support</RouterLink>
              <RouterLink to="/signup" class="text-gray-400 hover:text-white transition-colors">Get Started</RouterLink>
              <a href="/fees" class="text-gray-400 hover:text-white transition-colors">Fees</a>
              <a href="/security" class="text-gray-400 hover:text-white transition-colors">Security</a>
            </div>
          </div>

          <!-- Products & Services -->
          <div class="space-y-6">
            <h3 class="text-lg font-semibold">Products &amp; Services</h3>
            <div class="grid grid-cols-1 gap-3">
              <a href="#" class="text-gray-400 hover:text-white transition-colors">Spot Trading</a>
              <a href="#" class="text-gray-400 hover:text-white transition-colors">Margin Trading</a>
              <a href="#" class="text-gray-400 hover:text-white transition-colors">API Services</a>
              <a href="#" class="text-gray-400 hover:text-white transition-colors">Mobile App</a>

              <div class="flex items-center space-x-4 pt-2">
                <a
                  href="#"
                  class="p-2 bg-gray-700 rounded-lg hover:bg-gray-600 transition-colors group"
                  aria-label="App Store"
                >
                  <Store class="h-6 w-6 text-white group-hover:text-app-secondary transition-colors" />
                </a>
                <a
                  href="#"
                  class="p-2 bg-gray-700 rounded-lg hover:bg-gray-600 transition-colors group"
                  aria-label="Play Store"
                >
                  <Smartphone class="h-6 w-6 text-white group-hover:text-app-secondary transition-colors" />
                </a>
              </div>
            </div>
          </div>

          <!-- Connect With Us -->
          <div class="space-y-6">
            <h3 class="text-lg font-semibold">Connect With Us</h3>
            <div class="space-y-4">
              <div class="flex flex-col space-y-2">
                <span class="text-gray-400 text-sm">Address</span>
                <p class="text-white font-semibold">Soluyman, 108 Av. République</p>
              </div>

              <div class="flex flex-col space-y-2">
                <span class="text-gray-400 text-sm">24/7 Support</span>
                <a href="tel:+21672290512" class="text-white font-semibold hover:text-blue-400 transition-colors">+216 72 290 512</a>
              </div>

              <div class="flex flex-col space-y-2">
                <span class="text-gray-400 text-sm">Email Us</span>
                <a href="mailto:admin@bitchest.com" class="text-white font-semibold hover:text-blue-400 transition-colors">admin@bitchest.com</a>
              </div>

              <div class="space-y-2">
                <span class="text-gray-400 text-sm">Follow Us</span>
                <div class="flex space-x-4">
                  <a
                    v-for="social in socialLinks"
                    :key="social.name"
                    :href="social.url"
                    class="p-2 bg-gray-700 rounded-lg hover:bg-gray-600 transition-colors group"
                    target="_blank"
                    rel="noopener noreferrer"
                    :aria-label="`Follow on ${social.name}`"
                  >
                    <component :is="social.icon" class="h-5 w-5 text-white group-hover:text-app-secondary transition-colors" />
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Bottom Footer -->
        <div class="border-t border-gray-700 py-6 md:py-8">
          <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
            <div class="text-gray-400 text-sm">
              © {{ year }} Bitchest. All rights reserved.
            </div>

            <div class="flex flex-wrap justify-center gap-4 text-sm">
              <a href="/privacy" class="text-gray-400 hover:text-white transition-colors">Privacy Policy</a>
              <a href="/terms" class="text-gray-400 hover:text-white transition-colors">Terms of Service</a>
              <a href="/legal" class="text-gray-400 hover:text-white transition-colors">Legal Notice</a>
              <a href="/cookies" class="text-gray-400 hover:text-white transition-colors">Cookie Policy</a>
            </div>

            <!-- badges (commented out, kept for future use) -->
            <!--
            <div class="flex items-center space-x-3">
              <img :src="sslBadge" alt="SSL Secured" class="h-10" />
              <img :src="pciBadge" alt="PCI Compliant" class="h-10" />
            </div>
            -->
          </div>
        </div>
      </div>
    </footer>
  </ScrollReveal>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router';
import ScrollReveal from '@/components/ScrollReveal.vue';
import SectionBackground from './SectionBackground.vue';
import {
  Shield,
  CheckCircle,
  Facebook,
  Instagram,
  Linkedin,
  Github,
  Smartphone,
  Store
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
/* Footer styling relies mostly on Tailwind utilities. Add overrides here if needed. */
</style>