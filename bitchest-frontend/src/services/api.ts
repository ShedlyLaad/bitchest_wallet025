import axios, { AxiosError, type AxiosResponse } from 'axios';
import { cacheService } from './cacheService';
import type {
  AdminCreateUserPayload,
  AdminCreateUserResponse,
  AuthResponse,
  AuthUser,
  BuyPayload,
  CryptoCurrency,
  CryptoPricePoint,
  Paginated,
  PortfolioResponse,
  RegisterPayload,
  SellPayload,
  Transaction
} from '@/types';

const baseURL = import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8000';

const api = axios.create({
  baseURL,
  withCredentials: false, // Bearer token auth doesn't need cookies
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  }
});

let currentToken: string | null = null;


export function setAuthToken(token: string | null) {
  currentToken = token;
  if (token) {
    api.defaults.headers.common.Authorization = `Bearer ${token}`;
  } else {
    delete api.defaults.headers.common.Authorization;
  }
}

// Cache pour éviter les logs répétés de la même erreur
const errorLogCache = new Map<string, number>();
const ERROR_LOG_THROTTLE_MS = 2000; // Ne log qu'une fois toutes les 2 secondes pour la même erreur

api.interceptors.response.use(
  (response: AxiosResponse) => response,
  async (error: AxiosError<any>) => {
    const originalRequest = error.config as any;
    
    // Normaliser la structure de l'erreur pour faciliter l'affichage
    const errorResponse = error.response?.data || {};
    const status = error.response?.status;
    const url = error.config?.url || 'unknown endpoint';
    
    
    // Créer une clé unique pour cette erreur (sans le message pour éviter les doublons)
    const errorKey = `${status}-${url}`;
    const now = Date.now();
    const lastLogTime = errorLogCache.get(errorKey) || 0;
    
    // Fonction helper pour logger seulement si pas de throttle
    const shouldLog = () => {
      if (now - lastLogTime > ERROR_LOG_THROTTLE_MS) {
        errorLogCache.set(errorKey, now);
        // Nettoyer le cache après 10 secondes
        setTimeout(() => errorLogCache.delete(errorKey), 10000);
        return true;
      }
      return false;
    };
    
    // Handle 400 Bad Request - afficher le message d'erreur
    // NOTE: Certains proxies/serveurs transforment 401 en 400 pour les routes de login
    // On détecte cela en vérifiant le message d'erreur et l'URL
    if (status === 400) {
      const message = errorResponse.message || errorResponse.error || 'Requête invalide';
      
      // Détecter si c'est une erreur de login transformée (401 -> 400)
      const isLoginRoute = originalRequest?.url?.includes('/login') || originalRequest?.url?.includes('/register');
      const isLoginErrorMessage = message.includes('invalide') || 
                                   message.includes('Invalid credentials') || 
                                   message.includes('Email ou mot de passe') ||
                                   message.toLowerCase().includes('invalid');
      
      if (isLoginRoute && isLoginErrorMessage) {
        // C'est probablement une erreur 401 transformée en 400 par un proxy/middleware
        // On la traite comme une 401 pour l'affichage
        if (shouldLog()) {
          console.warn('[API] 400 reçu pour login (probablement 401 transformée):', message, {
            url,
            method: error.config?.method,
            originalStatus: status,
            message: message
          });
        }
        
        // Traiter comme une 401 pour l'affichage utilisateur
        error.message = message;
        if (error.response && error.response.data) {
          error.response.data = { ...errorResponse, message, error: message };
        }
        
        // Ne pas nettoyer le token pour les erreurs de login
        // Ne pas retry automatiquement
        return Promise.reject(error);
      }
      
      // Erreur 400 normale (pas de login)
      if (shouldLog()) {
        console.error('[API] 400 Bad Request:', message, {
          url,
          method: error.config?.method,
          status: status,
          data: errorResponse
        });
      }
      
      // Enrichir l'erreur avec un message clair
      error.message = message;
      if (!errorResponse.message && error.response && error.response.data) {
        error.response.data = { ...errorResponse, message };
      }
    }
    
    // Handle 401 Unauthorized - token expired or invalid
    // NOTE: Certains serveurs/proxies (comme XAMPP/Apache) peuvent transformer 401 en 400
    // On détecte cela en vérifiant le message d'erreur et l'URL
    const isLoginRoute = originalRequest?.url?.includes('/login') || originalRequest?.url?.includes('/register');
    const isLoginErrorMessage = errorResponse.message?.includes('invalide') || 
                                errorResponse.error?.includes('invalide') ||
                                errorResponse.message?.toLowerCase().includes('invalid credentials');
    
    // Si c'est une route de login avec un message d'erreur de login, traiter comme 401
    if (status === 401 || (status === 400 && isLoginRoute && isLoginErrorMessage)) {
      const message = errorResponse.message || errorResponse.error || 'Not authenticated. Please sign in again.';
      
      // Si c'était un 400 mais avec le message de login, c'est une transformation 401->400
      if (status === 400 && isLoginRoute) {
        // C'est probablement une erreur 401 transformée en 400 par un proxy/middleware (XAMPP/Apache)
        // On la traite comme une 401 pour l'affichage
        if (shouldLog()) {
          console.warn('[API] Status 400 reçu pour login (probablement 401 transformée par serveur):', message, {
            url,
            method: error.config?.method
          });
        }
      } else if (shouldLog()) {
        // Logger pour toutes les erreurs 401 (login/register ou token expiré)
        const logMessage = isLoginRoute
          ? '[API] 401 Unauthorized (Login/Register):'
          : '[API] 401 Unauthorized (Token expired):';
        console.error(logMessage, message, {
          url,
          method: error.config?.method,
          status: status
        });
      }
      
      // Clear token if present (mais pas pour les erreurs de login/register)
      if (currentToken && !isLoginRoute) {
        currentToken = null;
        setAuthToken(null);
      }
      
      // Enrichir l'erreur
      error.message = message;
      if (!errorResponse.message && !errorResponse.error && error.response && error.response.data) {
        error.response.data = { ...errorResponse, message, error: message };
      }
      
      // Ne pas retry automatiquement les requêtes d'authentification
      if (isLoginRoute) {
        return Promise.reject(error);
      }
    }
    
    // Handle 404 - Better error message
    if (status === 404) {
      const message = errorResponse.message || errorResponse.error || `Ressource introuvable: ${url}`;
      if (shouldLog()) {
        console.error('[API] 404 Not Found:', message, {
          url,
          method: error.config?.method,
          baseURL: error.config?.baseURL
        });
      }
      
      // Enrichir l'erreur
      error.message = message;
      if (!errorResponse.message && !errorResponse.error && error.response && error.response.data) {
        error.response.data = { ...errorResponse, message, error: message };
      }
    }
    
    // Handle 422 Validation errors
    if (status === 422) {
      const validationErrors = errorResponse.errors || {};
      const message = errorResponse.message || 'Validation error';
      if (shouldLog()) {
        console.error('[API] 422 Validation Error:', message, validationErrors);
      }
      
      // Enrichir l'erreur
      error.message = message;
      if (error.response && error.response.data && !errorResponse.message) {
        error.response.data = { ...errorResponse, message };
      }
    }
    
    // Handle network errors
    if (!error.response && error.code === 'ERR_NETWORK') {
      const message = 'Network error. Please check your internet connection.';
      if (shouldLog()) {
        console.error('[API] Network Error:', error.message, {
          url,
          baseURL: error.config?.baseURL
        });
      }
      
      // Créer une structure d'erreur cohérente
      error.message = message;
      error.response = {
        status: 0,
        statusText: 'Network Error',
        data: { message, error: message }
      } as any;
    }
    
    // Handle 500 Server errors
    if (status === 500) {
      const message = errorResponse.message || 'Server error. Please try again later.';
      if (shouldLog()) {
        console.error('[API] 500 Server Error:', message, {
          url,
          status
        });
      }
      
      error.message = message;
      if (error.response && error.response.data && !errorResponse.message) {
        error.response.data = { ...errorResponse, message };
      }
    }
    
    // Log other errors
    if (status && 
        status !== 400 && 
        status !== 401 && 
        status !== 404 && 
        status !== 422 && 
        status !== 500) {
      const message = errorResponse.message || errorResponse.error || error.message || 'Something went wrong';
      if (shouldLog()) {
        console.error('[API] Error:', message, { 
          status,
          url 
        });
      }
      
      error.message = message;
    }
    
    return Promise.reject(error);
  }
);

// Ensure Authorization header is present on all requests if we have a token
api.interceptors.request.use((config) => {
  // Ne pas ajouter le token pour les routes publiques
  const publicRoutes = ['/api/login', '/api/register', '/api/public/market'];
  const isPublicRoute = publicRoutes.some(route => config.url?.includes(route));
  
  if (currentToken && !isPublicRoute) {
    config.headers = { 
      ...(config.headers || {}), 
      Authorization: `Bearer ${currentToken}` 
    } as any;
  }
  
  return config;
}, (error) => {
  return Promise.reject(error);
});

// No longer needed - using Bearer token authentication only
export async function sanctum() {
  // This function is kept for backward compatibility but does nothing
  // Bearer token authentication doesn't require CSRF cookies
}

export async function login(payload: { email: string; password: string }): Promise<AuthResponse> {
  const { data } = await api.post<AuthResponse>('/api/login', payload);
  if (data.token) setAuthToken(data.token);
  return data;
}

export async function changePassword(payload: { current_password: string; password: string; password_confirmation: string }) {
  const { data } = await api.post<{ message: string; status: string }>('/api/change-password', payload);
  return data;
}

export async function register(payload: RegisterPayload) {
  const { data } = await api.post('/api/register', payload);
  return data as { message: string; status?: string; must_change_password?: boolean; temporary_password_sent?: boolean };
}

export async function logout() {
  try {
    if (currentToken) {
      await api.post('/api/logout');
    }
  } catch (error) {
    // Ignorer les erreurs de logout (token peut être déjà expiré)
    console.warn('[API] Logout error (ignored):', error);
  } finally {
    // Toujours nettoyer le token localement
    currentToken = null;
    setAuthToken(null);
  }
}

export async function fetchUser(): Promise<AuthUser | null> {
  try {
    const { data } = await api.get<AuthUser>('/api/user');
    return data;
  } catch (error: any) {
    // 401 ou 404 signifie que l'utilisateur n'est pas authentifié
    if (error?.response?.status === 401 || error?.response?.status === 404) {
      // Nettoyer le token si présent
      if (currentToken) {
        currentToken = null;
        setAuthToken(null);
      }
      return null;
    }
    throw error;
  }
}

// CLIENT
export async function getPortfolio(useCache: boolean = true): Promise<PortfolioResponse> {
  const cacheKey = 'portfolio';
  
  if (useCache) {
    return cacheService.preload(
      cacheKey,
      async () => {
        const { data } = await api.get<PortfolioResponse>('/api/portfolio');
        return data;
      },
      { ttl: 60 * 1000 } // 1 minute
    );
  }
  
  const { data } = await api.get<PortfolioResponse>('/api/portfolio');
  cacheService.set(cacheKey, data, { ttl: 60 * 1000 });
  return data;
}

export async function getPurchaseDetails(cryptoCurrencyId: number) {
  const { data } = await api.get<{ purchases: Array<{ id: number; date: string; datetime: string; quantity: number; price: number; total_cost: number }> }>(`/api/portfolio/crypto/${cryptoCurrencyId}/purchases`);
  return data;
}

export async function buyCrypto(payload: BuyPayload): Promise<{ transaction: Transaction; balance: number }> {
  try {
    const { data } = await api.post<{ transaction: Transaction; balance: number; message: string }>('/api/transaction/buy', payload);
    // Invalider les caches liés après une transaction pour forcer le rafraîchissement
    cacheService.remove('portfolio');
    cacheService.remove('transaction_history_1_10');
    cacheService.remove('user_cryptos');
    cacheService.remove('market');
    return { transaction: data.transaction, balance: data.balance };
  } catch (error: any) {
    // L'erreur est déjà formatée par l'intercepteur, on la propage
    throw error;
  }
}

export async function sellCrypto(payload: SellPayload): Promise<{ transaction: Transaction; balance: number }> {
  try {
    const { data } = await api.post<{ transaction: Transaction; balance: number; message: string }>('/api/transaction/sell', payload);
    // Invalider les caches liés après une transaction pour forcer le rafraîchissement
    cacheService.remove('portfolio');
    cacheService.remove('transaction_history_1_10');
    cacheService.remove('user_cryptos');
    cacheService.remove('market');
    return { transaction: data.transaction, balance: data.balance };
  } catch (error: any) {
    // L'erreur est déjà formatée par l'intercepteur, on la propage
    throw error;
  }
}

export async function getTransactionHistory(params?: { page?: number; per_page?: number }, useCache: boolean = true): Promise<Paginated<Transaction>> {
  const page = params?.page || 1;
  const cacheKey = `transaction_history_${page}_${params?.per_page || 10}`;
  
  if (useCache && page === 1) {
    // Cache uniquement la première page pour affichage rapide
    return cacheService.preload(
      cacheKey,
      async () => {
        const { data } = await api.get<Paginated<Transaction>>('/api/transaction/history', { params });
        return data;
      },
      { ttl: 2 * 60 * 1000 } // 2 minutes
    );
  }
  
  const { data } = await api.get<Paginated<Transaction>>('/api/transaction/history', { params });
  if (page === 1) {
    cacheService.set(cacheKey, data, { ttl: 2 * 60 * 1000 });
  }
  return data;
}

// NOTIFICATIONS
export async function getNotifications(params?: { page?: number; per_page?: number; unread_only?: boolean }, useCache: boolean = true) {
  const page = params?.page || 1;
  const unreadOnly = params?.unread_only ? 'unread' : 'all';
  const cacheKey = `notifications_${page}_${unreadOnly}`;
  
  if (useCache && page === 1) {
    return cacheService.preload(
      cacheKey,
      async () => {
        const { data } = await api.get<Paginated<any>>('/api/notifications', { params });
        return data;
      },
      { ttl: 10 * 1000 } // 10 secondes
    );
  }
  
  const { data } = await api.get<Paginated<any>>('/api/notifications', { params });
  if (page === 1) {
    cacheService.set(cacheKey, data, { ttl: 10 * 1000 });
  }
  return data;
}

export async function getUnreadNotificationsCount(useCache: boolean = true) {
  const cacheKey = 'notifications_unread_count';
  
  if (useCache) {
    return cacheService.preload(
      cacheKey,
      async () => {
        const { data } = await api.get<{ count: number }>('/api/notifications/unread-count');
        return data;
      },
      { ttl: 10 * 1000 } // 10 secondes
    );
  }
  
  const { data } = await api.get<{ count: number }>('/api/notifications/unread-count');
  cacheService.set(cacheKey, data, { ttl: 10 * 1000 });
  return data;
}

export async function markNotificationAsRead(id: number) {
  const { data } = await api.post<{ message: string }>(`/api/notifications/${id}/read`);
  return data;
}

export async function markAllNotificationsAsRead() {
  const { data } = await api.post<{ message: string; count: number }>('/api/notifications/read-all');
  return data;
}

export async function deleteNotification(id: number) {
  const { data } = await api.delete<{ message: string }>(`/api/notifications/${id}`);
  return data;
}

/**
 * Récupère les données du marché (route publique, pas d'auth requise)
 */
export async function getPublicMarket(useCache: boolean = true): Promise<CryptoCurrency[]> {
  const cacheKey = 'public_market';
  
  // Fonction helper pour normaliser les données
  const normalizeData = (data: CryptoCurrency[]) => {
    if (!Array.isArray(data)) return [];
    return data.map(crypto => {
      const price = typeof crypto.price === 'number' && !isNaN(crypto.price) && crypto.price > 0
        ? Number(Number(crypto.price).toFixed(8))
        : 0;
      let change24h = typeof crypto.change24h === 'number' && !isNaN(crypto.change24h)
        ? Number(crypto.change24h)
        : 0;
      // Limiter entre -99% et +200% (double vérification frontend)
      if (change24h < -99) change24h = -99;
      if (change24h > 200) change24h = 200;
      change24h = Number(change24h.toFixed(2));
      
      return {
        ...crypto,
        price,
        change24h
      };
    });
  };
  
  try {
    if (useCache) {
      return await cacheService.preload(
        cacheKey,
        async () => {
          const { data } = await api.get<CryptoCurrency[]>('/api/public/market');
          return normalizeData(data);
        },
        { ttl: 60 * 60 * 1000 } // 1 heure
      );
    }
    
    const { data } = await api.get<CryptoCurrency[]>('/api/public/market');
    const normalizedData = normalizeData(data);
    cacheService.set(cacheKey, normalizedData, { ttl: 60 * 60 * 1000 }); // 1 heure
    return normalizedData;
  } catch (error: any) {
    // En cas d'erreur (404, network, etc.), retourner un tableau vide plutôt que de faire planter l'app
    console.warn('[getPublicMarket] Failed to load public market:', error?.response?.status, error?.message);
    // Retourner les données en cache si disponibles
    const cached = cacheService.get<CryptoCurrency[]>(cacheKey);
    return cached || [];
  }
}

export async function getMarket(useCache: boolean = true) {
  const cacheKey = 'market';
  
  // Fonction helper pour normaliser les données
  const normalizeData = (data: CryptoCurrency[]) => {
    if (!Array.isArray(data)) return [];
    return data.map(crypto => {
      const price = typeof crypto.price === 'number' && !isNaN(crypto.price) && crypto.price > 0
        ? Number(Number(crypto.price).toFixed(8))
        : 0;
      let change24h = typeof crypto.change24h === 'number' && !isNaN(crypto.change24h)
        ? Number(crypto.change24h)
        : 0;
      // Limiter entre -99% et +200% (double vérification frontend)
      if (change24h < -99) change24h = -99;
      if (change24h > 200) change24h = 200;
      change24h = Number(change24h.toFixed(2));
      
      return {
        ...crypto,
        price,
        change24h
      };
    });
  };
  
  if (useCache) {
    return cacheService.preload(
      cacheKey,
      async () => {
        const { data } = await api.get<CryptoCurrency[]>('/api/market');
        return normalizeData(data);
      },
      { ttl: 60 * 60 * 1000 } // 1 heure
    );
  }
  
  const { data } = await api.get<CryptoCurrency[]>('/api/market');
  const normalizedData = normalizeData(data);
  cacheService.set(cacheKey, normalizedData, { ttl: 60 * 60 * 1000 }); // 1 heure
  return normalizedData;
}

/**
 * Force le rafraîchissement des prix crypto uniquement (alias pour refreshCryptoPrices)
 */
export async function refreshMarketPrices(): Promise<CryptoCurrency[]> {
  return refreshCryptoPrices();
}

export async function getUserCryptos(useCache: boolean = true) {
  const cacheKey = 'user_cryptos';
  
  if (useCache) {
    return cacheService.preload(
      cacheKey,
      async () => {
        const { data } = await api.get<CryptoCurrency[]>('/api/user/cryptos');
        // S'assurer que les valeurs sont correctement formatées et validées
        return Array.isArray(data) ? data.map(crypto => {
          const price = typeof crypto.price === 'number' && !isNaN(crypto.price) && crypto.price > 0
            ? Number(Number(crypto.price).toFixed(8))
            : 0;
          let change24h = typeof crypto.change24h === 'number' && !isNaN(crypto.change24h)
            ? Number(crypto.change24h)
            : 0;
          // Limiter entre -99% et +200% (double vérification frontend)
          if (change24h < -99) change24h = -99;
          if (change24h > 200) change24h = 200;
          change24h = Number(change24h.toFixed(2));
          
          return {
          ...crypto,
            price,
            change24h
          };
        }) : data;
      },
      { ttl: 60 * 60 * 1000 } // 1 heure
    );
  }
  
  const { data } = await api.get<CryptoCurrency[]>('/api/user/cryptos');
  // S'assurer que les valeurs sont correctement formatées et validées
  const formattedData = Array.isArray(data) ? data.map(crypto => {
    const price = typeof crypto.price === 'number' && !isNaN(crypto.price) && crypto.price > 0
      ? Number(Number(crypto.price).toFixed(8))
      : 0;
    let change24h = typeof crypto.change24h === 'number' && !isNaN(crypto.change24h)
      ? Number(crypto.change24h)
      : 0;
    // Limiter entre -99% et +200% (double vérification frontend)
    if (change24h < -99) change24h = -99;
    if (change24h > 200) change24h = 200;
    change24h = Number(change24h.toFixed(2));
    
    return {
    ...crypto,
      price,
      change24h
    };
  }) : data;
  cacheService.set(cacheKey, formattedData, { ttl: 60 * 60 * 1000 }); // 1 heure
  return formattedData;
}

/**
 * Force le rafraîchissement des prix crypto uniquement
 * Invalide le cache et récupère les nouvelles données depuis l'API
 */
export async function refreshCryptoPrices(): Promise<CryptoCurrency[]> {
  // Invalider tous les caches liés aux prix crypto
  cacheService.remove('user_cryptos');
  cacheService.remove('market');
  
  // Forcer le rafraîchissement sans utiliser le cache
  const data = await getUserCryptos(false);
  return data;
}

export async function getMarketHistory(symbol: string, useCache: boolean = true) {
  const cacheKey = `market_history_${symbol}`;
  
  if (useCache) {
    return cacheService.preload(
      cacheKey,
      async () => {
        const { data } = await api.get<CryptoPricePoint[]>(`/api/market/history/${symbol}`);
        return data;
      },
      { ttl: 60 * 1000 } // 1 minute
    );
  }
  
  const { data } = await api.get<CryptoPricePoint[]>(`/api/market/history/${symbol}`);
  cacheService.set(cacheKey, data, { ttl: 60 * 1000 });
  return data;
}

// ADMIN
export async function getAdminUsers() {
  const { data } = await api.get<AuthUser[]>('/api/admin/users');
  return data;
}

export async function createAdminUser(payload: AdminCreateUserPayload) {
  const { data } = await api.post<AdminCreateUserResponse>('/api/admin/users', payload);
  return data;
}

export async function approveUser(id: number) {
  const { data } = await api.post<{ message: string; user: AuthUser }>(`/api/admin/users/${id}/approve`);
  return data;
}

export async function blockUser(id: number) {
  const { data } = await api.post<{ message: string; user: AuthUser }>(`/api/admin/users/${id}/block`);
  return data;
}

export async function updateProfile(payload: { first_name: string; last_name: string; phone: string }) {
  const { data } = await api.put<{ message: string; user: AuthUser }>('/api/profile', payload);
  return data;
}

export async function uploadProfilePicture(file: File) {
  const formData = new FormData();
  formData.append('profile_picture', file);
  const { data } = await api.post<{ message: string; path: string; url: string; user: AuthUser }>('/api/profile/picture', formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  });
  return data;
}

export async function uploadProfileBanner(file: File) {
  const formData = new FormData();
  formData.append('profile_banner', file);
  const { data } = await api.post<{ message: string; path: string; url: string; user: AuthUser }>('/api/profile/banner', formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  });
  return data;
}

export async function deleteProfilePicture() {
  const { data } = await api.delete<{ message: string; user: AuthUser }>('/api/profile/picture');
  return data;
}

export async function deleteProfileBanner() {
  const { data } = await api.delete<{ message: string; user: AuthUser }>('/api/profile/banner');
  return data;
}

export async function deleteUser(id: number) {
  const { data } = await api.delete<{ message: string }>(`/api/admin/users/${id}`);
  return data;
}

export async function updateAdminUser(id: number, payload: { first_name?: string; last_name?: string }) {
  const { data } = await api.put<{ message: string; user: AuthUser }>(`/api/admin/users/${id}`, payload);
  return data;
}

export async function getAdminTransactions(params?: { user_id?: number; symbol?: string; type?: 'buy' | 'sell'; per_page?: number; page?: number }) {
  const { data } = await api.get<Paginated<Transaction>>('/api/admin/transactions', { params });
  return data;
}

export async function getAdminCryptos() {
  const { data } = await api.get<CryptoCurrency[]>('/api/admin/cryptos');
  return data;
}

export async function generateAdminPrices() {
  const { data } = await api.post<{ message: string }>('/api/admin/cryptos/generate');
  return data;
}

export async function getAdminCryptoHistory(symbol: string, timeframe?: string) {
  const params = timeframe ? { timeframe } : {};
  const { data } = await api.get<CryptoPricePoint[]>(`/api/admin/cryptos/${symbol}/history`, { params });
  return data;
}

export async function getAdminUserDetails(id: number) {
  const { data } = await api.get(`/api/admin/users/${id}`);
  return data as {
    user: AuthUser;
    balance: number;
    portfolio: any[];
    statistics: {
      total_transactions: number;
      buy_transactions: number;
      sell_transactions: number;
      total_volume: number;
      total_portfolio_value: number;
      total_invested: number;
      total_gain_loss: number;
      total_gain_loss_percent: number;
    };
    recent_transactions: Transaction[];
  };
}

export async function getAdminDashboard(timeFilter?: string, useCache: boolean = true) {
  const params = timeFilter ? { time_filter: timeFilter } : {};
  const cacheKey = `admin_dashboard_${timeFilter || 'default'}`;
  
  if (useCache) {
    return cacheService.preload(
      cacheKey,
      async () => {
        const { data } = await api.get('/api/admin/dashboard', { params });
        return data as {
          totals: {
            total_users: number;
            active_users: number;
            pending_validation: number;
            euro_balance: number;
            total_revenue: number;
            trades_count: number;
          };
          revenue_series: number[];
          trades_series: number[];
          pending_users: { id: number; name: string; email: string; submitDate: string }[];
          recent_activities: { id: number; user: string; action: string; time: string }[];
        };
      },
      { ttl: 60 * 1000 } // 1 minute
    );
  }
  
  const { data } = await api.get('/api/admin/dashboard', { params });
  cacheService.set(cacheKey, data, { ttl: 60 * 1000 });
  return data as {
    totals: {
      total_users: number;
      active_users: number;
      pending_validation: number;
      euro_balance: number;
      total_revenue: number;
      trades_count: number;
    };
    revenue_series: number[];
    trades_series: number[];
    pending_users: { id: number; name: string; email: string; submitDate: string }[];
    recent_activities: { id: number; user: string; action: string; time: string }[];
  };
}

// exposed default instance for ad-hoc calls
export default {
  instance: api,
  sanctum,
  login,
  logout,
  changePassword,
  fetchUser,
  updateProfile,
  uploadProfilePicture,
  uploadProfileBanner,
  deleteProfilePicture,
  deleteProfileBanner,
  getPortfolio,
  buyCrypto,
  sellCrypto,
  getTransactionHistory,
  getMarket,
  getPublicMarket,
  getUserCryptos,
  getMarketHistory,
  refreshCryptoPrices,
  refreshMarketPrices,
  getAdminUsers,
  createAdminUser,
  approveUser,
  blockUser,
  deleteUser,
  getAdminTransactions,
  getAdminCryptos,
  generateAdminPrices,
  getAdminCryptoHistory,
  getAdminDashboard,
  getAdminUserDetails,
  getPurchaseDetails
};




