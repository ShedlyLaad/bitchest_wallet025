/**
 * Composable pour utiliser facilement le cache dans les composants
 * Affiche immédiatement les données en cache puis met à jour en arrière-plan
 */

import { ref, onMounted } from 'vue';
import { cacheService } from '@/services/cacheService';

export function useCachedData<T>(
  cacheKey: string,
  fetcher: () => Promise<T>,
  options: {
    ttl?: number;
    useSessionStorage?: boolean;
    immediate?: boolean;
  } = {}
) {
  const data = ref<T | null>(null);
  const loading = ref(false);
  const error = ref<Error | null>(null);
  const fromCache = ref(false);

  const load = async (forceRefresh = false) => {
    if (loading.value && !forceRefresh) return;

    loading.value = true;
    error.value = null;

    try {
      if (forceRefresh) {
        // Forcer le rafraîchissement
        const freshData = await fetcher();
        cacheService.set(cacheKey, freshData, {
          ttl: options.ttl,
          useSessionStorage: options.useSessionStorage
        });
        data.value = freshData;
        fromCache.value = false;
      } else {
        // Utiliser le cache avec preload
        const cachedData = await cacheService.preload(
          cacheKey,
          fetcher,
          {
            ttl: options.ttl,
            useSessionStorage: options.useSessionStorage
          }
        );
        data.value = cachedData;
        fromCache.value = cacheService.has(cacheKey);
      }
    } catch (err) {
      error.value = err instanceof Error ? err : new Error('Unknown error');
      console.error(`[useCachedData] Error loading "${cacheKey}":`, err);
    } finally {
      loading.value = false;
    }
  };

  const refresh = () => load(true);

  const clearCache = () => {
    cacheService.remove(cacheKey);
    data.value = null;
  };

  if (options.immediate !== false) {
    onMounted(() => {
      load();
    });
  }

  return {
    data,
    loading,
    error,
    fromCache,
    load,
    refresh,
    clearCache
  };
}

