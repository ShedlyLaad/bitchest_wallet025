<template>
  <section class="relative py-24 overflow-hidden" :style="{ background: 'linear-gradient(135deg, #0a0f1a 0%, #1a2332 50%, #0a0f1a 100%)' }">
    <!-- Animated background canvas -->
    <div class="absolute inset-0">
      <canvas ref="canvasRef" class="absolute inset-0 w-full h-full"></canvas>
      <div class="absolute inset-0 opacity-60" :style="{ background: 'linear-gradient(135deg, rgba(10, 15, 26, 0.95) 0%, rgba(26, 35, 50, 0.80) 50%, rgba(10, 15, 26, 0.95) 100%)' }"></div>
    </div>

    <!-- Holographic grid with brand colors -->
    <div class="absolute inset-0 opacity-[0.08] pointer-events-none z-0">
      <div
        class="absolute inset-0"
        :style="{
          backgroundImage: `linear-gradient(to right, rgba(53, 167, 255, 0.15) 1px, transparent 1px), linear-gradient(to bottom, rgba(53, 167, 255, 0.15) 1px, transparent 1px)`,
          backgroundSize: '80px 80px',
          transform: 'perspective(1000px) rotateX(60deg)'
        }"
      />
    </div>

    <!-- Decorative lights with more transparency -->
    <div class="absolute top-1/4 left-1/4 w-96 h-96 rounded-full blur-3xl pointer-events-none" :style="{ backgroundColor: 'var(--blue-dark)', opacity: 0.08 }"></div>
    <div class="absolute bottom-1/3 right-1/4 w-80 h-80 rounded-full blur-3xl pointer-events-none" :style="{ backgroundColor: 'var(--blue)', opacity: 0.08 }"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      <div class="text-center mb-20">
        <Motion tag="h2"
          :initial="{ opacity: 0, y: 20 }"
          :animate="{ opacity: 1, y: 0 }"
          :transition="{ duration: 0.8, ease: 'easeOut' }"
          class="text-5xl md:text-6xl font-bold mb-8"
        >
          <span class="text-transparent bg-clip-text" :style="titleGradient">
            Why Choose Bit-Chest?
          </span>
        </Motion>

        <Motion tag="p"
          :initial="{ opacity: 0, y: 20 }"
          :animate="{ opacity: 1, y: 0 }"
          :transition="{ duration: 0.8, delay: 0.2, ease: 'easeOut' }"
          class="text-xl text-gray-300 max-w-3xl mx-auto leading-relaxed"
        >
          We provide the tools and security you need to trade cryptocurrencies with confidence.
        </Motion>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
        <Motion tag="div"
           v-for="(feature, index) in features"
           :key="index"
          :initial="{ opacity: 0, y: 30 }"
          :animate="{ opacity: 1, y: 0 }"
          :transition="{ duration: 0.6, delay: index * 0.2, ease: 'easeOut' }"
          :while-hover="{ y: -10, transition: { duration: 0.3 } }"
          class="relative group"
         >
           <!-- Glow around card (use inline gradient style so Tailwind JIT won't miss classes) -->
          <div class="absolute -inset-0.5 rounded-2xl blur-lg opacity-30 group-hover:opacity-70 transition-all duration-500"
               :style="glowStyle(feature)" />

          <!-- Card content -->
          <div
             class="relative bg-gray-800/70 backdrop-blur-xl rounded-2xl p-8 border border-gray-700/30 transition-all duration-500 h-full flex flex-col overflow-hidden group-hover:border-opacity-60"
             :style="{ borderColor: feature.borderColor }"
             >
             <!-- Animated rings behind icon with brand colors -->
             <div class="absolute -top-20 -left-20 w-40 h-40 opacity-20 group-hover:opacity-40 transition-opacity duration-500">
               <div 
                 class="absolute inset-0 border rounded-full animate-ping" 
                 :style="{ borderColor: feature.glowColor, animationDuration: '4s' }"
               ></div>
               <div 
                 class="absolute inset-4 border rounded-full animate-ping" 
                 :style="{ borderColor: feature.glowColor, animationDuration: '6s', animationDelay: '1s' }"
               ></div>
             </div>

            <div class="mb-8 p-4 rounded-full w-20 h-20 flex items-center justify-center transition-transform duration-500 group-hover:rotate-[15deg] group-hover:scale-110 relative z-10"
                 :style="cardGradientStyle(feature)">
               <div class="bg-gray-900 rounded-full w-16 h-16 flex items-center justify-center shadow-lg">
                 <component :is="feature.icon" class="h-12 w-12 text-white" />
               </div>
               <!-- Glow effect on hover -->
               <div 
                 class="absolute inset-0 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500 blur-xl"
                 :style="{ backgroundColor: feature.glowColor }"
               ></div>
             </div>

            <h3
               class="text-2xl font-bold mb-4 text-white transition-all duration-500"
               @mouseenter="titleHover($event, true)"
               @mouseleave="titleHover($event, false)"
             >
               {{ feature.title }}
             </h3>

             <p class="text-gray-300 leading-relaxed flex-grow">{{ feature.description }}</p>

             <!-- Hover light effect with brand colors -->
             <div class="absolute inset-0 rounded-2xl overflow-hidden opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none">
               <div 
                 class="absolute -top-[100%] -left-[100%] w-[300%] h-[300%] bg-gradient-to-r from-transparent via-transparent to-transparent group-hover:animate-spin-slow"
                 :style="{ background: `linear-gradient(to right, transparent, ${feature.glowColor}20, transparent)` }"
               ></div>
             </div>

             <!-- Bottom accent line -->
             <div 
               class="absolute bottom-0 left-0 right-0 h-1 opacity-0 group-hover:opacity-100 transition-opacity duration-500"
               :style="{ background: `linear-gradient(to right, ${feature.colorStart}, ${feature.colorEnd})` }"
             ></div>
          </div>
         </Motion>
       </div>
    </div>

    <!-- Decorative borders -->
    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-blue-500 to-transparent opacity-20" />
    <div class="absolute bottom-0 left-0 w-full h-1 opacity-20" :style="{ background: 'linear-gradient(to right, transparent, var(--blue-dark), transparent)' }" />
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Motion } from '@motionone/vue';
import { Shield, Zap, Users } from 'lucide-vue-next';
 
 const features = [
   {
     icon: Shield,
     title: 'Bank-Level Security',
     description: 'Your funds are protected by military-grade encryption and cold storage.',
     // Using brand colors
     colorStart: 'var(--blue-dark)', // #38618C
     colorEnd: 'var(--blue)',        // #35A7FF
     borderColor: 'rgba(53, 167, 255, 0.35)',
     glowColor: '#35A7FF'
   },
   {
     icon: Zap,
     title: 'Lightning Fast',
     description: 'Execute trades in milliseconds with our advanced matching engine.',
     colorStart: 'var(--accent-green)', // #01FF19
     colorEnd: 'var(--blue)',           // #35A7FF
     borderColor: 'rgba(1, 255, 25, 0.35)',
     glowColor: '#01FF19'
   },
   {
     icon: Users,
     title: '24/7 Support',
     description: 'Our expert team is always here to help you succeed in crypto trading.',
     colorStart: 'var(--blue)',        // #35A7FF
     colorEnd: 'var(--blue-dark)',     // #38618C
     borderColor: 'rgba(56, 97, 140, 0.35)',
     glowColor: '#38618C'
   }
 ];
 
 const titleGradient = {
   background: 'linear-gradient(to right, var(--blue), var(--blue-dark), var(--blue))',
   WebkitBackgroundClip: 'text',
   WebkitTextFillColor: 'transparent'
 };
 
 /* Canvas particle system */
 const canvasRef = ref<HTMLCanvasElement | null>(null);
 
 onMounted(() => {
   const canvas = canvasRef.value;
   if (!canvas) return;
   const ctx = canvas.getContext('2d');
   if (!ctx) return;
 
   const resize = () => {
     canvas.width = canvas.clientWidth;
     canvas.height = canvas.clientHeight;
   };
   resize();
 
   const particles: {
     x: number;
     y: number;
     size: number;
     speedX: number;
     speedY: number;
     color: string;
   }[] = [];
 
   const particleCount = 150;
   const colors = ['rgba(56, 189, 248, 0.3)', 'rgba(139, 92, 246, 0.3)', 'rgba(16, 185, 129, 0.3)'];
 
   for (let i = 0; i < particleCount; i++) {
     particles.push({
       x: Math.random() * canvas.width,
       y: Math.random() * canvas.height,
       size: Math.random() * 3 + 1,
       speedX: (Math.random() - 0.5) * 0.5,
       speedY: (Math.random() - 0.5) * 0.5,
       color: colors[Math.floor(Math.random() * colors.length)]
     });
   }
 
   let animationFrameId = 0;
 
   const animate = () => {
     if (!ctx || !canvas) return;
     ctx.clearRect(0, 0, canvas.width, canvas.height);
 
     particles.forEach(p => {
       p.x += p.speedX;
       p.y += p.speedY;
 
       if (p.x < 0) p.x = canvas.width;
       if (p.x > canvas.width) p.x = 0;
       if (p.y < 0) p.y = canvas.height;
       if (p.y > canvas.height) p.y = 0;
 
       ctx.beginPath();
       ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
       ctx.fillStyle = p.color;
       ctx.fill();
 
       particles.forEach(p2 => {
         const dx = p.x - p2.x;
         const dy = p.y - p2.y;
         const dist = Math.sqrt(dx * dx + dy * dy);
 
         if (dist < 100) {
           const opacity = 1 - dist / 100;
           ctx.beginPath();
           ctx.strokeStyle = `rgba(99, 102, 241, ${opacity * 0.1})`;
           ctx.lineWidth = 0.5;
           ctx.moveTo(p.x, p.y);
           ctx.lineTo(p2.x, p2.y);
           ctx.stroke();
         }
       });
     });
 
     animationFrameId = requestAnimationFrame(animate);
   };
 
   animate();
 
   const onResize = () => {
     resize();
   };
   window.addEventListener('resize', onResize);
 
   onBeforeUnmount(() => {
     cancelAnimationFrame(animationFrameId);
     window.removeEventListener('resize', onResize);
   });
 });
 
 /* Small DOM handlers to mimic original inline hover effects */
 function titleHover(e: Event, enter = true) {
   const el = e.currentTarget as HTMLElement;
   if (enter) {
     el.style.background = 'linear-gradient(to right, var(--blue), var(--blue-dark))';
     (el.style as any).WebkitBackgroundClip = 'text';
     (el.style as any).WebkitTextFillColor = 'transparent';
   } else {
     el.style.background = '';
     (el.style as any).WebkitBackgroundClip = '';
     (el.style as any).WebkitTextFillColor = '';
     el.style.color = 'white';
   }
 }
 
/* Helper styles (used inline to avoid Tailwind JIT dynamic-class pitfalls) */
function cardGradientStyle(feature: any) {
  return {
    backgroundImage: `linear-gradient(135deg, ${feature.colorStart}, ${feature.colorEnd})`
  };
}

function glowStyle(feature: any) {
  // subtle glowing gradient behind the card
  return {
    backgroundImage: `linear-gradient(90deg, ${feature.colorStart}, ${feature.colorEnd})`
  };
}
</script>

<style scoped>
/* rely on Tailwind utilities; keep minor animation helper */
@keyframes spin-slow {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
.group-hover\:animate-spin-slow:hover { animation: spin-slow 8s linear infinite; }
</style>