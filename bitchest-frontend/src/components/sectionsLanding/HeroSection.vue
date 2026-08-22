<template>
  <section
    ref="containerRef"
    class="relative min-h-screen flex items-center overflow-hidden"
  >
    <SectionBackground />

    <!-- Subtle light that follows the pointer, kept faint -->
    <div
      class="absolute inset-0 pointer-events-none transition-all duration-300 ease-out"
      :style="{
        background: `radial-gradient(circle at ${mousePosition.x}px ${mousePosition.y}px, rgba(53, 167, 255, 0.08) 0%, rgba(56, 97, 140, 0.05) 40%, transparent 70%)`,
        willChange: 'background-position'
      }"
    />

    <!-- Content wrapper -->
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20 z-20 w-full">
      <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
        <!-- Left content -->
        <div 
          class="space-y-6 md:space-y-8 text-center lg:text-left"
          :style="{
            opacity: entered ? 1 : 0,
            transform: entered ? 'translateY(0)' : 'translateY(30px)',
            transition: 'opacity 0.8s ease-out, transform 0.8s cubic-bezier(.2,.9,.3,1)'
          }"
        >
          <div class="space-y-6">
            <div class="inline-block">
              <div
                class="inline-flex items-center gap-2 px-4 py-2 backdrop-blur-md rounded-full border mb-6 transition-all duration-300 hover:scale-105"
                :style="{ 
                  backgroundColor: 'rgba(56, 97, 140, 0.15)', 
                  borderColor: 'var(--blue)',
                  boxShadow: '0 0 20px rgba(53, 167, 255, 0.2)'
                }"
              >
                <span class="h-2 w-2 rounded-full animate-pulse" style="background-color: var(--accent-green)"></span>
                <span class="text-sm font-medium" style="color: var(--blue)">Bitchest Platform</span>
                <span class="text-xs px-2 py-0.5 rounded-full" :style="{ backgroundColor: 'rgba(1, 255, 25, 0.2)', color: 'var(--accent-green)' }">Live</span>
              </div>
            </div>

            <h1 class="text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-bold leading-tight">
              <span class="block text-white mb-2">
                Master the Future of
              </span>
              <span
                ref="animatedTitle"
                class="block mt-2 bg-clip-text text-transparent"
                :style="{
                  background: 'linear-gradient(to right, var(--accent-green), var(--blue), var(--blue-dark), var(--accent-green))',
                  WebkitBackgroundClip: 'text',
                  WebkitTextFillColor: 'transparent',
                  backgroundSize: '200% 200%',
                  backgroundPosition: titleBgPos
                }"
              >
                Crypto Finance Platform
              </span>
            </h1>

            <p class="text-lg lg:text-xl text-gray-300 leading-relaxed max-w-2xl">
              Your gateway to mastering the world of cryptocurrencies — combining real-time insights, smart portfolio management, and beginner-friendly tools to help you thrive in digital finance.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 pt-2">
              <router-link
                to="/signin"
                class="group relative flex items-center justify-center text-white px-8 py-4 rounded-xl font-semibold transition-all duration-300 transform hover:scale-[1.05] shadow-xl overflow-hidden"
                :style="{ 
                  background: 'linear-gradient(to right, var(--accent-green), var(--blue))',
                  boxShadow: '0 0 30px rgba(1, 255, 25, 0.4), 0 0 20px rgba(53, 167, 255, 0.3)'
                }"
              >
                <span class="relative z-10">Get Started Today</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 transition-transform group-hover:translate-x-1 relative z-10" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
                <!-- Animated background on hover -->
                <div class="absolute inset-0 rounded-xl bg-white opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                <!-- Shine effect -->
                <div class="absolute inset-0 -translate-x-full group-hover:translate-x-full transition-transform duration-700 bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
              </router-link>

              <router-link
                to="/features"
                class="group relative flex items-center justify-center px-8 py-4 rounded-xl font-semibold transition-all duration-300 backdrop-blur-sm bg-white/5 border border-white/10"
              >
                <span>Explore Features</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 transition-transform group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/>
                </svg>
              </router-link>
            </div>
          </div>
        </div>

        <!-- Right content (illustration) -->
        <div class="relative flex justify-center items-center">
          <div class="relative w-full max-w-2xl h-[600px] flex items-center justify-center">
            <!-- Rotating rings with icons -->
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
              <div
                v-for="i in 2"
                :key="`ring-${i}`"
                class="absolute rounded-full flex items-center justify-center"
                :style="ringStyle(i - 1)"
              >
              <div
                v-for="(crypto, j) in cryptoIcons"
                :key="`ring-${i}-icon-${j}-${crypto.symbol}`"
                class="absolute"
                :style="iconOnRingStyle(i - 1, j, cryptoIcons.length)"
              >
                <div
                  class="w-8 h-8 rounded-full flex items-center justify-center"
                  :style="{
                    background: `linear-gradient(145deg, ${crypto.color}40, ${crypto.color}20)`,
                    boxShadow: `0 0 15px ${crypto.color}30`
                  }"
                >
                  <img :src="crypto.icon" :alt="crypto.symbol" class="w-4 h-4" @error="(e: any) => e.target.style.display = 'none'" />
                </div>
              </div>
              </div>
            </div>

            <!-- Central card -->
            <div
              class="absolute z-20 transform transition-all duration-500 hover:scale-105"
              :style="{ 
                transform: entered ? 'scale(1) rotate(0deg)' : 'scale(0.96) rotate(-5deg)',
                opacity: entered ? 1 : 0,
                transition: 'transform 0.7s cubic-bezier(.2,.9,.3,1), opacity 0.7s ease-out'
              }"
            >
              <div class="w-80 h-96 rounded-2xl overflow-hidden backdrop-blur-xl border border-white/10 shadow-2xl" :style="{ background: 'linear-gradient(to bottom right, rgba(53,167,255,0.1), rgba(56,97,140,0.1))', boxShadow: '0 0 50px rgba(53,167,255,0.1)' }">
                <div class="p-5 h-full flex flex-col">
                  <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center" :style="{ background: 'linear-gradient(to bottom right, var(--blue-dark), var(--blue))' }">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                    </div>
                    <div>
                      <div class="font-medium text-white">Crypto Portfolio</div>
                      <div class="text-xs" style="color: var(--blue)">Active Dashboard</div>
                    </div>
                  </div>

                  <div v-if="!loadingCryptos && cryptoIcons.length > 0" class="flex-1 grid grid-cols-2 gap-3">
                    <div
                      v-for="(crypto, idx) in cryptoIcons.slice(0,4)"
                      :key="`small-${idx}-${crypto.price}-${crypto.change24h}`"
                      class="rounded-xl p-3 backdrop-blur-sm bg-white/5 border border-white/5 transition-colors cursor-pointer hover:-translate-y-1"
                      :style="{ borderColor: hoveredIndex === idx ? crypto.color : '', backgroundColor: hoveredIndex === idx ? crypto.color + '15' : '' }"
                      @mouseenter="hoveredIndex = idx"
                      @mouseleave="hoveredIndex = -1"
                    >
                      <div class="flex items-center gap-2">
                        <img :src="crypto.icon" :alt="crypto.symbol" class="w-6 h-6" @error="(e: any) => e.target.style.display = 'none'" />
                        <div class="text-sm font-medium text-white">{{ crypto.symbol }}</div>
                      </div>
                      <div class="mt-2 text-xs font-mono text-gray-300">€{{ (crypto.price || 0).toFixed(2) }}</div>
                      <div :class="['mt-1 text-xs', (crypto.change24h || 0) >= 0 ? 'text-green-400' : 'text-red-400']">
                        {{ (crypto.change24h || 0) >= 0 ? '↑' : '↓' }} {{ Math.abs(crypto.change24h || 0).toFixed(2) }}%
                      </div>
                    </div>
                  </div>
                  <div v-else-if="loadingCryptos" class="flex-1 flex items-center justify-center">
                    <div class="text-gray-400 text-sm">Loading prices...</div>
                  </div>
                  <div v-else class="flex-1 flex items-center justify-center">
                    <div class="text-gray-400 text-sm">No data available</div>
                  </div>

                  <div class="mt-6 pt-4 border-t border-white/10">
                    <div class="flex items-center justify-between">
                      <div class="text-xs text-gray-400">Live market data</div>
                      <div class="flex items-center gap-1.5 text-xs font-medium" style="color: var(--accent-green)">
                        <span class="h-1.5 w-1.5 rounded-full animate-pulse" style="background-color: var(--accent-green)"></span>
                        {{ cryptoIcons.length }} assets tracked
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- central light glow with brand colors -->
            <div class="absolute inset-0 z-10 pointer-events-none" :style="{ background: 'radial-gradient(circle at center, rgba(53, 167, 255, 0.15) 0%, rgba(56, 97, 140, 0.1) 40%, transparent 70%)', mixBlendMode: 'screen', opacity: 0.9 }" />
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, onBeforeUnmount, computed } from 'vue';
import axios from 'axios';
import type { CryptoCurrency } from '../../types';
import { getCryptoIcon } from '../../utils/cryptoIcons';
import SectionBackground from './SectionBackground.vue';

const baseURL = import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8000';
const api = axios.create({
  baseURL,
  withCredentials: true
});

// Utility: color map
const getColorForCrypto = (symbol: string) => {
  const colors: Record<string, string> = {
    BTC: '#F7931A',
    ETH: '#627EEA',
    XRP: '#23292F',
    BCH: '#8DC351',
    ADA: '#0033AD',
    LTC: '#345D9D',
    XEM: '#67B2E8',
    XLM: '#14B6E7',
    MIOTA: '#00d4ff',
    DASH: '#008de4'
  };
  return colors[symbol] || '#627EEA';
};

// Refs & reactive state
const containerRef = ref<HTMLElement | null>(null);
const mousePosition = reactive({ x: 0, y: 0 });
const entered = ref(false);
const titleBgPos = ref('0% 50%');
const hoveredIndex = ref<number>(-1);
const loadingCryptos = ref(true);

// Crypto data from API
const cryptoData = ref<CryptoCurrency[]>([]);

// Prepare crypto icons (first 8) from API data
const cryptoIcons = computed(() => {
  return cryptoData.value.slice(0, 8).map(c => ({
    icon: getCryptoIcon(c.symbol),
    color: getColorForCrypto(c.symbol),
    symbol: c.symbol,
    price: c.price || 0,
    change24h: c.change24h || 0,
    name: c.name
  }));
});

// Load cryptos from API (public route for landing page)
const loadCryptos = async () => {
  try {
    loadingCryptos.value = true;
    const { data } = await api.get<CryptoCurrency[]>('/api/public/market');
    cryptoData.value = Array.isArray(data) ? data.map(c => ({
      ...c,
      price: typeof c.price === 'number' ? Number(c.price) : Number(c.price || 0),
      change24h: typeof c.change24h === 'number' ? Number(c.change24h) : Number(c.change24h || 0)
    })) : [];
  } catch (error: any) {
    console.error('Error loading cryptos for HeroSection:', error);
    cryptoData.value = [];
  } finally {
    loadingCryptos.value = false;
  }
};

// Animation loop / pointer handling
let titleRafId: number | null = null;

const clamp = (v: number, a: number, b: number) => Math.max(a, Math.min(b, v));

const updateCssMouseVars = () => {
  if (!containerRef.value) return;
  containerRef.value.style.setProperty('--mouse-x', `${mousePosition.x}px`);
  containerRef.value.style.setProperty('--mouse-y', `${mousePosition.y}px`);
};

const onPointerMove = (e: PointerEvent) => {
  if (!containerRef.value) return;
  const rect = containerRef.value.getBoundingClientRect();
  const x = clamp((e.clientX - rect.left), 0, rect.width);
  const y = clamp((e.clientY - rect.top), 0, rect.height);

  // smoother values (use normalized to container for some calculations if needed)
  mousePosition.x = Math.round(x);
  mousePosition.y = Math.round(y);

  // update CSS vars immediately for GPU-accelerated repaints
  updateCssMouseVars();
};

const onPointerLeave = () => {
  // center fallback when leaving
  if (!containerRef.value) return;
  const rect = containerRef.value.getBoundingClientRect();
  mousePosition.x = Math.round(rect.width / 2);
  mousePosition.y = Math.round(rect.height / 2);
  updateCssMouseVars();
};

// Title background pan via RAF (smoother than setInterval)
let titlePos = 0;
let titleDir = 1;
const titleAnimate = () => {
  titlePos += titleDir * 0.2;
  if (titlePos > 100) titlePos = 0;
  if (titlePos < 0) titlePos = 100;
  titleBgPos.value = `${titlePos.toFixed(2)}% 50%`;
  titleRafId = requestAnimationFrame(titleAnimate);
};

onMounted(async () => {
  entered.value = true;

  // Load crypto data from API
  await loadCryptos();

  // pointer events on container (covers mouse + touch + pen)
  if (containerRef.value) {
    containerRef.value.addEventListener('pointermove', onPointerMove, { passive: true });
    containerRef.value.addEventListener('pointerleave', onPointerLeave, { passive: true });
    // init CSS vars to center
    const rect = containerRef.value.getBoundingClientRect();
    mousePosition.x = Math.round(rect.width / 2);
    mousePosition.y = Math.round(rect.height / 2);
    updateCssMouseVars();
  }

  // Start animation loop
  if (!titleRafId) titleRafId = requestAnimationFrame(titleAnimate);
});

onBeforeUnmount(() => {
  if (containerRef.value) {
    containerRef.value.removeEventListener('pointermove', onPointerMove);
    containerRef.value.removeEventListener('pointerleave', onPointerLeave);
  }
  if (titleRafId) {
    cancelAnimationFrame(titleRafId);
    titleRafId = null;
  }
});

// Helpers for ring styles & icon placement
const ringStyle = (index: number) => {
  if (cryptoIcons.value.length === 0) {
    return { opacity: 0, display: 'none' };
  }
  const size = 400 + index * 100;
  const deg = 90 + index * 30;
  const rotateDur = 30 + index * 10;
  return {
    width: `${size}px`,
    height: `${size}px`,
    background: `linear-gradient(${deg}deg, rgba(53, 167, 255, 0.1), rgba(56, 97, 140, 0.05))`,
    border: '1px solid rgba(53, 167, 255, 0.2)',
    filter: 'blur(0.5px)',
    borderRadius: '9999px',
    animation: `spin ${rotateDur}s linear infinite`
  };
};

const iconOnRingStyle = (ringIndex: number, iconIndex: number, total: number) => {
  if (total === 0) return {};
  const angle = (iconIndex * Math.PI * 2) / total;
  const radius = (400 + ringIndex * 100) / 2;
  const x = Math.cos(angle) * radius;
  const y = Math.sin(angle) * radius;
  const delay = iconIndex * 0.15;
  return {
    left: `calc(50% + ${x}px)`,
    top: `calc(50% + ${y}px)`,
    transform: 'translate(-50%,-50%)',
    animation: `pulseScale 2.2s ${delay}s ease-in-out infinite`
  };
};

</script>

<style scoped>
/* use css vars exposed by script to drive background radial */
section[ref] {
  /* nothing here; we set vars on container element from JS */
}

/* update radial uses --mouse-x/--mouse-y fallback to center with brand colors */
.absolute.inset-0.pointer-events-none.transition-all.duration-300.ease-out {
  background: radial-gradient(circle at var(--mouse-x, 50%) var(--mouse-y, 50%), rgba(53, 167, 255, 0.12) 0%, rgba(56, 97, 140, 0.08) 40%, transparent 70%);
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
@keyframes pulseScale {
  0% { transform: translate(-50%, -50%) scale(1); opacity: 0.6; }
  50% { transform: translate(-50%, -50%) scale(1.2); opacity: 1; }
  100% { transform: translate(-50%, -50%) scale(1); opacity: 0.6; }
}
</style>