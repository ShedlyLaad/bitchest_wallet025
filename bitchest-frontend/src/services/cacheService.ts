/**
 * Cache Service - Système de cache intelligent pour accélérer l'affichage
 * Utilise localStorage pour persistance et sessionStorage pour données temporaires
 */

interface CacheEntry<T> {
  data: T;
  timestamp: number;
  expiresAt: number;
}

interface CacheConfig {
  ttl?: number; // Time to live en millisecondes
  useSessionStorage?: boolean; // Utiliser sessionStorage au lieu de localStorage
}

class CacheService {
  private readonly DEFAULT_TTL = 5 * 60 * 1000; // 5 minutes par défaut
  private readonly PREFIX = 'bitchest_cache_';
  
  /**
   * Génère une clé de cache normalisée
   */
  private getKey(key: string): string {
    return `${this.PREFIX}${key}`;
  }

  /**
   * Récupère les données du cache si elles sont valides
   */
  get<T>(key: string): T | null {
    const cacheKey = this.getKey(key);
    const storage = this.getStorage(key);
    
    try {
      const cached = storage.getItem(cacheKey);
      if (!cached) return null;

      const entry: CacheEntry<T> = JSON.parse(cached);
      const now = Date.now();

      // Vérifier si le cache a expiré
      if (now > entry.expiresAt) {
        this.remove(key);
        return null;
      }

      return entry.data;
    } catch (error) {
      console.error(`[Cache] Error reading cache for key "${key}":`, error);
      this.remove(key);
      return null;
    }
  }

  /**
   * Stocke les données dans le cache
   */
  set<T>(key: string, data: T, config: CacheConfig = {}): void {
    const cacheKey = this.getKey(key);
    const storage = this.getStorage(key, config.useSessionStorage);
    const ttl = config.ttl || this.getDefaultTTL(key);
    const now = Date.now();

    const entry: CacheEntry<T> = {
      data,
      timestamp: now,
      expiresAt: now + ttl
    };

    try {
      storage.setItem(cacheKey, JSON.stringify(entry));
    } catch (error) {
      console.error(`[Cache] Error writing cache for key "${key}":`, error);
      // Si le storage est plein, nettoyer les anciennes entrées
      this.cleanup();
      try {
        storage.setItem(cacheKey, JSON.stringify(entry));
      } catch (retryError) {
        console.error(`[Cache] Failed to write cache after cleanup:`, retryError);
      }
    }
  }

  /**
   * Supprime une entrée du cache
   */
  remove(key: string): void {
    const cacheKey = this.getKey(key);
    try {
      localStorage.removeItem(cacheKey);
      sessionStorage.removeItem(cacheKey);
    } catch (error) {
      console.error(`[Cache] Error removing cache for key "${key}":`, error);
    }
  }

  /**
   * Vérifie si une clé existe dans le cache et est valide
   */
  has(key: string): boolean {
    return this.get(key) !== null;
  }

  /**
   * Nettoie les entrées expirées du cache
   */
  cleanup(): void {
    const storages = [localStorage, sessionStorage];
    const now = Date.now();

    storages.forEach(storage => {
      try {
        const keysToRemove: string[] = [];
        
        for (let i = 0; i < storage.length; i++) {
          const key = storage.key(i);
          if (key && key.startsWith(this.PREFIX)) {
            try {
              const cached = storage.getItem(key);
              if (cached) {
                const entry: CacheEntry<any> = JSON.parse(cached);
                if (now > entry.expiresAt) {
                  keysToRemove.push(key);
                }
              }
            } catch {
              keysToRemove.push(key);
            }
          }
        }

        keysToRemove.forEach(key => storage.removeItem(key));
      } catch (error) {
        console.error('[Cache] Error during cleanup:', error);
      }
    });
  }

  /**
   * Vide tout le cache
   */
  clear(): void {
    const storages = [localStorage, sessionStorage];
    
    storages.forEach(storage => {
      try {
        const keysToRemove: string[] = [];
        
        for (let i = 0; i < storage.length; i++) {
          const key = storage.key(i);
          if (key && key.startsWith(this.PREFIX)) {
            keysToRemove.push(key);
          }
        }

        keysToRemove.forEach(key => storage.removeItem(key));
      } catch (error) {
        console.error('[Cache] Error during clear:', error);
      }
    });
  }

  /**
   * Récupère le storage approprié
   */
  private getStorage(key: string, useSessionStorage?: boolean): Storage {
    // Certaines données doivent être en sessionStorage (données sensibles)
    if (useSessionStorage || key.includes('session')) {
      return sessionStorage;
    }
    return localStorage;
  }

  /**
   * Détermine le TTL par défaut selon le type de données
   */
  private getDefaultTTL(key: string): number {
    // Données de marché : 30 secondes (changent souvent)
    if (key.includes('market') || key.includes('crypto') || key.includes('price')) {
      return 30 * 1000;
    }
    
    // Portfolio : 1 minute (change moins souvent)
    if (key.includes('portfolio')) {
      return 60 * 1000;
    }
    
    // Transactions : 2 minutes
    if (key.includes('transaction') || key.includes('history')) {
      return 2 * 60 * 1000;
    }
    
    // Notifications : 10 secondes (très dynamique)
    if (key.includes('notification')) {
      return 10 * 1000;
    }
    
    // Dashboard/Stats : 1 minute
    if (key.includes('dashboard') || key.includes('stats')) {
      return 60 * 1000;
    }
    
    // Données utilisateur : 5 minutes
    if (key.includes('user') || key.includes('profile')) {
      return 5 * 60 * 1000;
    }
    
    // Par défaut : 5 minutes
    return this.DEFAULT_TTL;
  }

  /**
   * Précharge les données critiques
   */
  async preload<T>(key: string, fetcher: () => Promise<T>, config: CacheConfig = {}): Promise<T> {
    // Vérifier d'abord le cache
    const cached = this.get<T>(key);
    if (cached !== null) {
      // Retourner immédiatement les données en cache
      // Puis mettre à jour en arrière-plan
      fetcher().then(data => {
        this.set(key, data, config);
      }).catch(error => {
        console.error(`[Cache] Error preloading "${key}":`, error);
      });
      return cached;
    }

    // Si pas de cache, faire l'appel API
    const data = await fetcher();
    this.set(key, data, config);
    return data;
  }
}

// Instance singleton
export const cacheService = new CacheService();

// Nettoyer le cache au démarrage
if (typeof window !== 'undefined') {
  cacheService.cleanup();
  
  // Nettoyer périodiquement (toutes les 10 minutes)
  setInterval(() => {
    cacheService.cleanup();
  }, 10 * 60 * 1000);
}

