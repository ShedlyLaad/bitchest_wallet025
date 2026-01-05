<template>
  <div class="skeleton-loader" :class="[variant, size]">
    <div class="skeleton-shimmer"></div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  variant?: 'text' | 'circular' | 'rectangular' | 'card';
  size?: 'small' | 'medium' | 'large';
  width?: string;
  height?: string;
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'text',
  size: 'medium',
  width: undefined,
  height: undefined
});
</script>

<style scoped>
.skeleton-loader {
  position: relative;
  overflow: hidden;
  background: linear-gradient(90deg, rgba(55, 65, 81, 0.3) 25%, rgba(75, 85, 99, 0.3) 50%, rgba(55, 65, 81, 0.3) 75%);
  background-size: 200% 100%;
  animation: skeleton-loading 1.5s ease-in-out infinite;
}

.skeleton-shimmer {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    90deg,
    transparent 0%,
    rgba(255, 255, 255, 0.1) 50%,
    transparent 100%
  );
  animation: shimmer 1.5s infinite;
}

/* Variants */
.text {
  border-radius: 4px;
  height: 1em;
}

.circular {
  border-radius: 50%;
  aspect-ratio: 1;
}

.rectangular {
  border-radius: 8px;
}

.card {
  border-radius: 12px;
  padding: 1rem;
}

/* Sizes */
.small {
  height: 0.75rem;
}

.medium {
  height: 1rem;
}

.large {
  height: 1.5rem;
}

/* Custom width/height via inline styles */
.skeleton-loader[style*="width"] {
  width: v-bind('props.width || "100%"');
}

.skeleton-loader[style*="height"] {
  height: v-bind('props.height || "auto"');
}

@keyframes skeleton-loading {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

@keyframes shimmer {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(100%);
  }
}
</style>

