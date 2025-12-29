<template>
  <ScrollReveal width="100%">
    <section class="relative py-32 overflow-hidden bg-gray-900/50">
      <!-- Advanced Background Effects -->
      <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <!-- Dynamic Gradient Orb -->
        <div class="orb-left"></div>

        <!-- Animated Vector Lines (SVG) -->
        <svg class="absolute inset-0 w-full h-full">
          <path
            d="M0,100 Q250,0 500,100 T1000,100"
            stroke="url(#gradient-line)"
            stroke-width="2"
            fill="none"
            class="svg-path-anim"
          />
          <defs>
            <linearGradient id="gradient-line" x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="0%" stop-color="#60A5FA" stop-opacity="0.2" />
              <stop offset="50%" stop-color="#C084FC" stop-opacity="0.2" />
              <stop offset="100%" stop-color="#34D399" stop-opacity="0.2" />
            </linearGradient>
          </defs>
        </svg>
      </div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-20 relative">
          <!-- Left Vector Art -->
          <div class="vector-left hidden lg:block" aria-hidden>
            <div class="vector-left-inner">
              <div class="geom-shape" />
              <div class="rotating-svg">
                <svg viewBox="0 0 100 100" class="w-full h-full opacity-20">
                  <path d="M50 10 L90 90 L10 90 Z" stroke="url(#gradient-left)" stroke-width="1" fill="none" class="svg-path-anim" />
                  <defs>
                    <linearGradient id="gradient-left" x1="0%" y1="0%" x2="100%" y2="0%">
                      <stop offset="0%" stop-color="#60A5FA" />
                      <stop offset="100%" stop-color="#C084FC" />
                    </linearGradient>
                  </defs>
                </svg>
              </div>

              <div class="left-lines">
                <div v-for="i in 3" :key="i" class="left-line" :style="{ top: `${(i-1) * 30}%`, animationDelay: `${(i-1)*0.5}s` }" />
              </div>
            </div>
          </div>

          <!-- Right Vector Art -->
          <div class="vector-right hidden lg:block" aria-hidden>
            <div class="circles-grid">
              <div v-for="i in 9" :key="i" class="circle-grid-item" :style="{ animationDelay: `${(i-1)*0.15}s` }" />
            </div>

            <div class="floating-hex">
              <svg viewBox="0 0 100 100" class="w-full h-full opacity-20">
                <path d="M50 0 L95 25 L95 75 L50 100 L5 75 L5 25 Z" stroke="url(#gradient-right)" stroke-width="1" fill="none" class="svg-path-anim" />
                <defs>
                  <linearGradient id="gradient-right" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#C084FC" />
                    <stop offset="100%" stop-color="#34D399" />
                  </linearGradient>
                </defs>
              </svg>
            </div>
          </div>

          <h2 class="text-5xl font-bold bg-clip-text text-transparent mb-4 tracking-tight relative z-10" style="background: linear-gradient(to right, var(--blue), var(--blue-dark), var(--accent-green)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            Dual Leadership
          </h2>
          <p class="text-gray-400 text-lg">Visionary founders leading the future of crypto</p>
        </div>

        <!-- Team cards -->
        <div class="flex flex-col md:flex-row justify-center items-center gap-8 md:gap-16 lg:gap-24 px-4 sm:px-6 lg:px-8">
          <!-- Chedi Card -->
          <div class="team-card group">
            <div class="card-inner">
              <div class="avatar-wrap">
                <div class="avatar-ring">
                  <img src="/chedi.jpg" alt="Chedi" class="avatar-img" />
                </div>
              </div>

              <div class="card-content">
                <h3 class="text-3xl font-bold text-center mb-3 gradient-text">Ouerghi Chedi</h3>
                <p class="text-gray-300 text-center mb-4 font-medium">Co-Founder &amp; CEO</p>
                <p class="text-gray-400 text-center text-sm leading-relaxed">
                  Étudiant à The Digital School, passionné par l'innovation et le leadership entrepreneurial.
                </p>
              </div>

              <!-- Dynamic background elements -->
              <div class="card-bg">
                <div v-for="i in 3" :key="i" class="bg-orb" :style="bgOrbStyle(i-1)" />
              </div>
            </div>
          </div>

          <!-- Shedly Card -->
          <div class="team-card group">
            <div class="card-inner">
              <div class="avatar-wrap">
                <div class="avatar-ring accent-ring">
                  <img src="/shedly.jpg" alt="Shedly" class="avatar-img" />
                </div>
              </div>

              <div class="card-content">
                <h3 class="text-3xl font-bold text-center mb-3 gradient-text-alt">Laadhiby Shedly</h3>
                <p class="text-gray-300 text-center mb-4 font-medium">Co-Founder &amp; CEO</p>
                <p class="text-gray-400 text-center text-sm leading-relaxed">
                  Étudiant à The Digital School, engagé dans la transformation digitale et les nouvelles technologies.
                </p>
              </div>

              <div class="card-bg">
                <div v-for="i in 3" :key="`s-${i}`" class="bg-orb alt" :style="bgOrbStyle(i-1, true)" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Decorative lines -->
      <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-blue-500 to-transparent opacity-20" />
      <div class="absolute bottom-0 left-0 w-full h-1 opacity-20" style="background: linear-gradient(to right, transparent, var(--blue-dark), transparent)"></div>
    </section>
  </ScrollReveal>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import ScrollReveal from '../ScrollReveal.vue';

const bgOrbStyle = (i: number, alt = false) => {
  const size = 100 + i * 50;
  const left = `${Math.random() * 100}%`;
  const top = `${Math.random() * 100}%`;
  const gradient = alt
    ? 'radial-gradient(circle, rgba(139,92,246,0.3) 0%, transparent 70%)'
    : 'radial-gradient(circle, rgba(96,165,250,0.3) 0%, transparent 70%)';
  // each orb gets a different animation delay via inline style
  return {
    background: gradient,
    width: `${size}px`,
    height: `${size}px`,
    left,
    top,
    animationDelay: `${i * 0.6}s`
  };
};
</script>

<style scoped>
/* Orb left (big rotating orb) */
.orb-left {
  position: absolute;
  top: 0;
  left: 0;
  width: 800px;
  height: 800px;
  border-radius: 9999px;
  filter: blur(48px);
  background: linear-gradient(to right, rgba(53,167,255,0.2), rgba(56,97,140,0.2), rgba(1,255,25,0.2));
  transform-origin: center;
  animation: rotate-slow 20s linear infinite, scale-pulse 20s ease-in-out infinite;
}

/* SVG path animation mimic (dash draw) */
.svg-path-anim {
  stroke-dasharray: 1000;
  stroke-dashoffset: 1000;
  animation: draw 4s linear infinite;
}

@keyframes draw {
  0% { stroke-dashoffset: 1000; opacity: 0.2; }
  50% { stroke-dashoffset: 0; opacity: 0.6; }
  100% { stroke-dashoffset: -1000; opacity: 0.2; }
}

@keyframes rotate-slow {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
@keyframes scale-pulse {
  0%,100% { transform: rotate(0) scale(1); }
  50% { transform: rotate(180deg) scale(1.08); }
}

/* Left/Right vector containers basic layout */
.vector-left, .vector-right { position: absolute; top: 50%; transform: translateY(-50%); width: 200px; height: 400px; pointer-events: none; }
.vector-left { left: 0; }
.vector-right { right: 0; }

/* Geometric shapes */
.geom-shape { width: 80px; height: 80px; border-radius: 9999px; filter: blur(18px);
  background: linear-gradient(to right, rgba(53,167,255,0.2), rgba(56,97,140,0.2));
  animation: geom-anim 8s infinite;
}
@keyframes geom-anim {
  0% { transform: scale(1) rotate(0deg); opacity: 0.5; }
  50% { transform: scale(1.2) rotate(90deg); opacity: 0.8; }
  100% { transform: scale(1) rotate(0deg); opacity: 0.5; }
}

/* Left animated vertical lines */
.left-line {
  position: absolute;
  left: 50%;
  width: 2px;
  transform-origin: bottom;
  height: 4rem;
  background-color: rgba(96,165,250,0.2);
  transform: rotate(25deg);
  animation: left-line-anim 3s ease-in-out infinite;
}
@keyframes left-line-anim {
  0% { height: 4rem; opacity: 0.3; }
  50% { height: 6rem; opacity: 0.6; }
  100% { height: 4rem; opacity: 0.3; }
}

/* Circles grid animation */
.circle-grid-item {
  width: 1rem;
  height: 1rem;
  border-radius: 9999px;
  background: linear-gradient(to right, rgba(56,97,140,0.2), rgba(1,255,25,0.2));
  animation: circle-pulse 4s ease-in-out infinite;
  margin: 0.5rem;
}
@keyframes circle-pulse {
  0% { transform: scale(1); opacity: 0.3; }
  50% { transform: scale(1.5); opacity: 0.6; }
  100% { transform: scale(1); opacity: 0.3; }
}
.circles-grid { position: absolute; inset: 0; display: grid; grid-template-columns: repeat(3, min-content); gap: 0.5rem; }

/* Floating hexagon */
.floating-hex { position: absolute; right: 2.5rem; top: 50%; transform: translateY(-50%); width: 6rem; height: 6rem; animation: float-hex 6s infinite; }
@keyframes float-hex {
  0% { transform: translateY(0) rotate(0deg); }
  50% { transform: translateY(-20px) rotate(60deg); }
  100% { transform: translateY(0) rotate(0deg); }
}

/* Team card styles */
.team-card { width: 100%; max-width: 20rem; perspective: 1000px; }
.card-inner { position: relative; z-index: 10; background: linear-gradient(180deg, rgba(31,41,55,0.8), rgba(17,24,39,0.8)); backdrop-filter: blur(18px); padding: 2rem; border-radius: 1rem; border: 1px solid rgba(113,128,150,0.12); overflow: hidden; box-shadow: 0 0 50px rgba(59,130,246,0.12); transform-style: preserve-3d; transition: transform 0.4s ease; }
.team-card:hover .card-inner { transform: translateZ(10px); }

/* Avatar */
.avatar-wrap { display:flex; justify-content:center; }
.avatar-ring { width: 10rem; height: 10rem; border-radius:9999px; padding: 0.25rem; box-shadow: 0 10px 30px rgba(0,0,0,0.5); background: linear-gradient(to bottom right, var(--blue), var(--blue-dark)); border: 1px solid rgba(53,167,255,0.12); display:flex; align-items:center; justify-content:center; overflow:hidden; }
.accent-ring { background: linear-gradient(to bottom right, var(--blue-dark), var(--accent-green)); box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
.avatar-img { width: 100%; height: 100%; object-fit: cover; border-radius: 9999px; transform-origin: center; transition: transform 0.5s ease; }
.team-card:hover .avatar-img { transform: scale(1.05); }

/* Card content gradient texts */
.gradient-text { background: linear-gradient(to right, var(--blue), var(--blue-dark)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.gradient-text-alt { background: linear-gradient(to right, var(--blue-dark), var(--accent-green)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

/* Background orbs inside card */
.card-bg { position: absolute; inset: 0; z-index: -10; pointer-events: none; }
.bg-orb { position: absolute; border-radius: 9999px; mix-blend-mode: overlay; animation: orb-move 5s ease-in-out infinite; opacity: 0.35; }
.bg-orb.alt { animation-direction: reverse; }
@keyframes orb-move {
  0% { transform: scale(1) translate(0,0); opacity: 0.3; }
  50% { transform: scale(1.2) translate(30px,-30px); opacity: 0.5; }
  100% { transform: scale(1) translate(0,0); opacity: 0.3; }
}
</style>