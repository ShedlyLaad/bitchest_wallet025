import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { MotionPlugin } from '@vueuse/motion';
import App from './App.vue';
import router from './router';
import './index.css';
import { preloader } from './utils/preloader';
import { useAuthStore } from './stores/auth';
import { updatePageHead } from './utils/pageTitle';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);
app.use(MotionPlugin);

// Précharger les données critiques après que l'app soit montée
app.mount('#app');

// Set initial page title and favicon based on current route
updatePageHead(router.currentRoute.value.name?.toString());

// Hydrater l'état d'authentification depuis le localStorage AVANT de vérifier si l'utilisateur est connecté
const auth = useAuthStore();
auth.hydrate();

// Précharger les données si l'utilisateur est déjà authentifié
if (auth.token) {
  // Attendre un peu pour que l'app soit complètement initialisée
  setTimeout(() => {
    preloader.preloadCriticalData();
  }, 500);
}

// Précharger aussi après navigation si l'utilisateur se connecte
router.afterEach((to) => {
  if (auth.token && (to.path.includes('/dashboard') || to.path.includes('/trade') || to.path.includes('/portfolio'))) {
    preloader.preloadCriticalData();
  }
});