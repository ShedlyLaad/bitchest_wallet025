import { ref, onMounted, onBeforeUnmount } from 'vue';

/**
 * Composable for animated background with particles canvas and holographic grid
 * Inspired by LogoIntro3D component
 */
export function useAnimatedBackground() {
  const particlesCanvas = ref<HTMLCanvasElement | null>(null);
  let animationFrameId: number | null = null;
  let resizeHandler: (() => void) | null = null;

  const initParticlesCanvas = () => {
    const canvas = particlesCanvas.value;
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    // Set canvas size
    const resizeCanvas = () => {
      if (!canvas) return;
      // Get container dimensions or use window size
      const rect = canvas.parentElement?.getBoundingClientRect();
      canvas.width = rect?.width || window.innerWidth;
      canvas.height = rect?.height || window.innerHeight;
    };

    resizeCanvas();

    const particleCount = 100;
    const particleArray: Array<{
      x: number;
      y: number;
      size: number;
      speedX: number;
      speedY: number;
      opacity: number;
    }> = [];

    // Initialize particles
    for (let i = 0; i < particleCount; i++) {
      particleArray.push({
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height,
        size: Math.random() * 2 + 1,
        speedX: (Math.random() - 0.5) * 0.5,
        speedY: (Math.random() - 0.5) * 0.5,
        opacity: Math.random() * 0.5 + 0.2
      });
    }

    const animateParticles = () => {
      if (!canvas || !ctx) return;
      ctx.clearRect(0, 0, canvas.width, canvas.height);

      particleArray.forEach(particle => {
        particle.x += particle.speedX;
        particle.y += particle.speedY;

        // Wrap around edges
        if (particle.x < 0) particle.x = canvas.width;
        if (particle.x > canvas.width) particle.x = 0;
        if (particle.y < 0) particle.y = canvas.height;
        if (particle.y > canvas.height) particle.y = 0;

        // Draw with brand colors
        const colorChoice = Math.random() > 0.5 ? 'rgba(53, 167, 255, ' : 'rgba(56, 97, 140, ';
        ctx.fillStyle = colorChoice + particle.opacity + ')';
        ctx.beginPath();
        ctx.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2);
        ctx.fill();
      });

      animationFrameId = requestAnimationFrame(animateParticles);
    };

    animateParticles();

    // Handle resize
    resizeHandler = () => {
      resizeCanvas();
      // Reposition particles after resize
      particleArray.forEach(particle => {
        particle.x = Math.min(particle.x, canvas.width);
        particle.y = Math.min(particle.y, canvas.height);
      });
    };

    window.addEventListener('resize', resizeHandler);
  };

  onMounted(() => {
    // Use nextTick to ensure canvas is mounted
    setTimeout(() => {
      if (particlesCanvas.value) {
        initParticlesCanvas();
      }
    }, 0);
  });

  onBeforeUnmount(() => {
    if (resizeHandler) {
      window.removeEventListener('resize', resizeHandler);
      resizeHandler = null;
    }
    if (animationFrameId) {
      cancelAnimationFrame(animationFrameId);
      animationFrameId = null;
    }
  });

  return {
    particlesCanvas
  };
}
