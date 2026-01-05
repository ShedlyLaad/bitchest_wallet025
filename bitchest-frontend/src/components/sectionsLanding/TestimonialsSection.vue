<template>
  <section class="relative py-24 overflow-hidden bg-gradient-to-b from-gray-950 to-gray-900">
    <!-- Particles background -->
    <div class="absolute inset-0">
      <div class="bg-orb-large left-orb" />
      <div class="bg-orb-large right-orb" />
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      <div class="text-center mb-16">
        <h2 class="text-5xl md:text-6xl font-bold mb-6 gradient-heading">
          What Our Users Say
        </h2>
        <p class="text-xl text-gray-300 max-w-3xl mx-auto leading-relaxed">
          Trusted by millions of traders worldwide who trust us with their crypto journey
        </p>
      </div>

      <div class="relative">
        <!-- Controls -->
        <button
          @click="prevTestimonial"
          class="control left-control"
          @mouseenter="hoverBorder($event, true)"
          @mouseleave="hoverBorder($event, false)"
          aria-label="Previous testimonial"
        >
          <ChevronLeft class="h-6 w-6" />
        </button>

        <button
          @click="nextTestimonial"
          class="control right-control"
          @mouseenter="hoverBorder($event, true)"
          @mouseleave="hoverBorder($event, false)"
          aria-label="Next testimonial"
        >
          <ChevronRight class="h-6 w-6" />
        </button>

        <!-- Testimonials container -->
        <div ref="containerRef" class="relative h-[500px] md:h-[450px] overflow-hidden">
          <transition-group name="testimonial" tag="div">
            <div
              v-for="(testimonial, index) in testimonials"
              :key="testimonial.id"
              v-show="index === activeIndex"
              class="absolute inset-0"
            >
              <div class="relative h-full">
                <div class="absolute -inset-0.5 rounded-3xl blur-xl opacity-50" style="background: linear-gradient(to right, rgba(56,97,140,0.2), rgba(53,167,255,0.2));"></div>

                <div class="relative h-full bg-gray-800/70 backdrop-blur-xl rounded-3xl p-8 border border-gray-700/30 flex flex-col md:flex-row items-center gap-10 overflow-hidden">
                  <div class="relative flex-shrink-0">
                    <div class="absolute -inset-4 rounded-full pulse-halo" style="background: linear-gradient(to right, rgba(56,97,140,0.3), rgba(53,167,255,0.3));"></div>
                    <div class="relative rounded-full overflow-hidden w-32 h-32 md:w-40 md:h-40 border-4 border-gray-700/50">
                      <div class="bg-gray-700 w-full h-full" />
                      <img :src="testimonial.avatar" :alt="testimonial.name" class="w-full h-full object-cover" />
                    </div>
                    <div class="absolute inset-0 rounded-full border-2 ping-border" style="animation-duration: 4s; border-color: rgba(56,97,140,0.3)"></div>
                  </div>

                  <div class="flex-grow">
                    <div class="flex mb-6">
                      <Star v-for="i in testimonial.rating" :key="i" class="h-6 w-6 text-yellow-400 md:h-8 md:w-8 star-anim" />
                    </div>

                    <blockquote class="text-xl md:text-2xl text-gray-200 leading-relaxed mb-8 italic relative quote">
                      <div class="quote-mark left">"</div>
                      {{ testimonial.content }}
                      <div class="quote-mark right">"</div>
                    </blockquote>

                    <div class="border-t border-gray-700/50 pt-6">
                      <div class="font-bold text-xl md:text-2xl text-white">{{ testimonial.name }}</div>
                      <div class="text-sm md:text-base text-app-primary">{{ testimonial.role }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </transition-group>
        </div>

        <!-- Pagination dots -->
        <div class="flex justify-center mt-10 space-x-2">
          <button
            v-for="(_, index) in testimonials"
            :key="index"
            @click="setActive(index)"
            :class="['dot', index === activeIndex ? 'active' : '']"
            :aria-label="`Go to testimonial ${index + 1}`"
          />
        </div>
      </div>

      <!-- Bottom stats -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-20">
        <div v-for="(stat, i) in bottomStats" :key="i" class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur-sm rounded-2xl p-6 border border-gray-700/30 text-center">
          <div class="text-3xl md:text-4xl font-bold bg-clip-text text-transparent mb-2" :style="{ background: 'linear-gradient(to right, var(--blue), var(--blue-dark))', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent' }">
            {{ stat.value }}
          </div>
          <div class="text-gray-400">{{ stat.label }}</div>
        </div>
      </div>
    </div>

    <!-- decorative lines -->
    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-blue-500 to-transparent opacity-20" />
    <div class="absolute bottom-0 left-0 w-full h-1 opacity-20" style="background: linear-gradient(to right, transparent, var(--blue-dark), transparent)"></div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Star, ChevronLeft, ChevronRight } from 'lucide-vue-next';

const testimonials = ref([
  {
    id: 1,
    name: 'Sarah Johnson',
    role: 'Professional Trader',
    content: "The best crypto platform I've used. Fast execution and excellent security make trading effortless and worry-free.",
    rating: 5,
    avatar: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=200'
  },
  {
    id: 2,
    name: 'Michael Chen',
    role: 'Investor',
    content: 'User-friendly interface with powerful trading tools. The analytics dashboard helped me increase my ROI by 35% in just two months.',
    rating: 5,
    avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200'
  },
  {
    id: 3,
    name: 'Emma Davis',
    role: 'Crypto Enthusiast',
    content: 'Amazing customer support and seamless trading experience. The mobile app is perfectly synced with the desktop platform.',
    rating: 5,
    avatar: 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=200'
  },
  {
    id: 4,
    name: 'Alex Rodriguez',
    role: 'Blockchain Developer',
    content: 'API integration is flawless. I built a custom trading bot in just 3 days using their well-documented developer tools.',
    rating: 5,
    avatar: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=200'
  }
]);

const activeIndex = ref(0);
const containerRef = ref<HTMLElement | null>(null);
let intervalId: number | null = null;

const nextTestimonial = () => {
  activeIndex.value = (activeIndex.value + 1) % testimonials.value.length;
};
const prevTestimonial = () => {
  activeIndex.value = (activeIndex.value - 1 + testimonials.value.length) % testimonials.value.length;
};
const setActive = (i: number) => {
  activeIndex.value = i;
};

onMounted(() => {
  intervalId = window.setInterval(() => {
    nextTestimonial();
  }, 5000);
});

onBeforeUnmount(() => {
  if (intervalId) window.clearInterval(intervalId);
});

const bottomStats = [
  { value: '4.9/5', label: 'Average Rating' },
  { value: '10M+', label: 'Active Users' },
  { value: '99.9%', label: 'Uptime' },
  { value: '24/7', label: 'Support' }
];

function hoverBorder(e: MouseEvent, enter: boolean) {
  const target = e.currentTarget as HTMLElement;
  if (!target) return;
  target.style.borderColor = enter ? 'var(--blue-dark)' : '';
}
</script>

<style scoped>
.gradient-heading { background: linear-gradient(to right, var(--blue), var(--blue-dark), var(--blue)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

/* Background orbs */
.bg-orb-large { position: absolute; width: 24rem; height: 24rem; border-radius: 9999px; filter: blur(48px); opacity: 0.1; }
.left-orb { left: 10%; top: 10%; background-color: var(--blue-dark); }
.right-orb { right: 10%; bottom: 10%; background-color: var(--blue); }

/* Controls */
.control { position: absolute; top: 50%; transform: translateY(-50%); z-index: 20; width: 3rem; height: 3rem; border-radius: 9999px; background: rgba(31,41,55,0.5); border: 1px solid rgba(55,65,81,1); display:flex; align-items:center; justify-content:center; color: #cbd5e1; cursor: pointer; box-shadow: 0 6px 18px rgba(0,0,0,0.5); }
.left-control { left: 0; transform: translate(-48%, -50%); }
.right-control { right: 0; transform: translate(48%, -50%); }

/* Pulse halo */
.pulse-halo { position:absolute; inset: -1rem; border-radius:9999px; filter: blur(12px); opacity: 0.9; animation: pulse 4s infinite; }
@keyframes pulse { 0% { transform: scale(1); opacity: 0.6 } 50% { transform: scale(1.08); opacity: 0.9 } 100% { transform: scale(1); opacity: 0.6 } }

/* Ping border */
.ping-border { position:absolute; inset:0; border-radius:9999px; animation: ping 2.5s infinite; pointer-events: none; }
@keyframes ping { 0% { transform: scale(1); opacity: 0.7 } 70% { transform: scale(1.2); opacity: 0 } 100% { transform: scale(1); opacity: 0 } }

/* Quote styling */
.quote { position: relative; }
.quote-mark { position: absolute; font-size: 4rem; font-family: serif; color: rgba(56,97,140,0.3); top: -1rem; }
.quote-mark.right { right: -1rem; color: rgba(53,167,255,0.3); top: auto; bottom: -1rem; }
.quote-mark.left { left: -1.5rem; top: -1rem; }

/* Star animation simple pop */
.star-anim { animation: star-pop 0.4s ease forwards; }
@keyframes star-pop { 0% { transform: scale(0); opacity:0 } 100% { transform: scale(1); opacity:1 } }

/* Transition-group for testimonials */
.testimonial-enter-from, .testimonial-leave-to { opacity: 0; transform: translateY(3rem) scale(0.95); }
.testimonial-enter-to, .testimonial-leave-from { opacity: 1; transform: translateY(0) scale(1); }
.testimonial-enter-active, .testimonial-leave-active { transition: all 0.8s cubic-bezier(.33,1,.68,1); }

/* Dots */
.dot { width: 0.75rem; height: 0.75rem; border-radius: 9999px; background: #374151; transition: all 0.3s; }
.dot.active { width: 2rem; background: linear-gradient(to right, var(--blue-dark), var(--blue)); }

/* Bottom stats already have tailwind, no extra styles required */
</style>