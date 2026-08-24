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
          <h2 class="text-5xl font-bold bg-clip-text text-transparent mb-4 tracking-tight relative z-10" style="background: linear-gradient(to right, var(--blue), var(--blue-dark), var(--accent-green)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            Meet the Founder
          </h2>
          <p class="text-gray-400 text-lg">The vision and engineering behind BitChest</p>
        </div>

        <!-- Founder card -->
        <div class="flex justify-center px-4 sm:px-6 lg:px-8">
          <div class="founder-card group">
            <div class="card-inner">
              <div class="avatar-wrap">
                <div class="avatar-ring">
                  <img src="/Fondateur.png" alt="Shedly Laadhiby" class="avatar-img" />
                </div>
              </div>

              <div class="card-content">
                <h3 class="text-4xl font-bold mb-2 gradient-text">Shedly Laadhiby</h3>
                <p class="text-gray-200 text-lg mb-4 font-medium">Founder &amp; Full-Stack Developer</p>
                <p class="text-gray-300 text-base leading-relaxed mb-5">
                  Full-Stack Developer with a background in Information Systems and Web &amp; Mobile Development.
                  Founder of Bitchets, responsible for its design, development, and technical direction, with a
                  focus on building practical and scalable digital solutions.
                </p>

                <div class="skills-row">
                  <span v-for="skill in skills" :key="skill" class="skill-chip">{{ skill }}</span>
                </div>
              </div>

              <!-- Dynamic background elements -->
              <div class="card-bg">
                <div v-for="i in 3" :key="i" class="bg-orb" :style="bgOrbStyle(i-1)" />
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
import ScrollReveal from '../ScrollReveal.vue';

const skills = ['Vue.js', 'TypeScript', 'Laravel', 'Tailwind CSS'];

const bgOrbStyle = (i: number) => {
  const size = 100 + i * 50;
  const left = `${Math.random() * 100}%`;
  const top = `${Math.random() * 100}%`;
  const gradient = 'radial-gradient(circle, rgba(96,165,250,0.3) 0%, transparent 70%)';
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

/* Founder card styles - landscape layout */
.founder-card { width: 100%; max-width: 64rem; perspective: 1000px; }
.card-inner {
  position: relative;
  z-index: 10;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2rem;
  background: linear-gradient(120deg, rgba(31,41,55,0.8), rgba(17,24,39,0.8));
  backdrop-filter: blur(18px);
  padding: 2.5rem;
  border-radius: 1.25rem;
  border: 1px solid rgba(113,128,150,0.12);
  overflow: hidden;
  box-shadow: 0 0 50px rgba(53,167,255,0.15);
  transform-style: preserve-3d;
  transition: transform 0.4s ease;
}
.founder-card:hover .card-inner { transform: translateZ(10px); }

@media (min-width: 768px) {
  .card-inner { flex-direction: row; align-items: center; text-align: left; padding: 3.5rem; gap: 3rem; }
}

/* Avatar */
.avatar-wrap { display:flex; justify-content:center; flex-shrink: 0; }
.avatar-ring { width: 12rem; height: 12rem; border-radius:9999px; padding: 0.35rem; box-shadow: 0 10px 30px rgba(0,0,0,0.5); background: linear-gradient(to bottom right, var(--blue), var(--blue-dark), var(--accent-green)); border: 1px solid rgba(53,167,255,0.12); display:flex; align-items:center; justify-content:center; overflow:hidden; }

@media (min-width: 768px) {
  .avatar-ring { width: 14rem; height: 14rem; }
}
.avatar-img { width: 100%; height: 100%; object-fit: cover; border-radius: 9999px; background: #111827; transform-origin: center; transition: transform 0.5s ease; }
.founder-card:hover .avatar-img { transform: scale(1.05); }

.card-content { text-align: center; }

@media (min-width: 768px) {
  .card-content { text-align: left; }
}

/* Card content gradient text */
.gradient-text { background: linear-gradient(to right, var(--blue), var(--blue-dark), var(--accent-green)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

/* Skills row */
.skills-row { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.5rem; }

@media (min-width: 768px) {
  .skills-row { justify-content: flex-start; }
}
.skill-chip {
  font-size: 0.8125rem;
  font-weight: 500;
  padding: 0.4rem 0.95rem;
  border-radius: 9999px;
  color: var(--blue);
  background: rgba(53,167,255,0.1);
  border: 1px solid rgba(53,167,255,0.25);
}

/* Background orbs inside card */
.card-bg { position: absolute; inset: 0; z-index: -10; pointer-events: none; }
.bg-orb { position: absolute; border-radius: 9999px; mix-blend-mode: overlay; animation: orb-move 5s ease-in-out infinite; opacity: 0.35; }
@keyframes orb-move {
  0% { transform: scale(1) translate(0,0); opacity: 0.3; }
  50% { transform: scale(1.2) translate(30px,-30px); opacity: 0.5; }
  100% { transform: scale(1) translate(0,0); opacity: 0.3; }
}
</style>
