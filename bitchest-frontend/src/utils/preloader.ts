/**
 * Preloader - Précharge les données critiques au démarrage de l'application
 */

import { getPortfolio, getUserCryptos, getUnreadNotificationsCount } from '@/services/api';
import { useAuthStore } from '@/stores/auth';

class Preloader {
  private preloaded = false;

  /**
   * Précharge les données critiques si l'utilisateur est authentifié
   */
  async preloadCriticalData() {
    if (this.preloaded) return;
    
    const auth = useAuthStore();
    
    // Vérifier si l'utilisateur est authentifié
    if (!auth.token || !auth.user) {
      return;
    }

    try {
      // Précharger en parallèle les données les plus importantes
      const promises: Promise<any>[] = [];

      // Portfolio (très important pour dashboard/portfolio)
      promises.push(
        getPortfolio(true).catch(err => {
          console.warn('[Preloader] Failed to preload portfolio:', err);
        })
      );

      // Cryptos du marché (important pour TradePage)
      promises.push(
        getUserCryptos(true).catch(err => {
          console.warn('[Preloader] Failed to preload cryptos:', err);
        })
      );

      // Notifications count (important pour toutes les pages)
      promises.push(
        getUnreadNotificationsCount(true).catch(err => {
          console.warn('[Preloader] Failed to preload notifications count:', err);
        })
      );

      // Attendre que toutes les données soient préchargées
      await Promise.allSettled(promises);
      
      this.preloaded = true;
      console.log('[Preloader] Critical data preloaded successfully');
    } catch (error) {
      console.error('[Preloader] Error during preload:', error);
    }
  }

  /**
   * Réinitialise le preloader (utile après logout)
   */
  reset() {
    this.preloaded = false;
  }
}

export const preloader = new Preloader();

