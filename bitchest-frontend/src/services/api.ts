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
  withCredentials: true,
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN'
});

api.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

let currentToken: string | null = null;
let csrfReady = false;

function getCookie(name: string): string | null {
  const match = document.cookie.match(new RegExp('(^|; )' + encodeURIComponent(name) + '=([^;]*)'));
  return match ? decodeURIComponent(match[2]) : null;
}

export function setAuthToken(token: string | null) {
  currentToken = token;
  if (token) {
    api.defaults.headers.common.Authorization = `Bearer ${token}`;
  } else {
    delete api.defaults.headers.common.Authorization;
  }
}

api.interceptors.response.use(
  (response: AxiosResponse) => response,
  async (error: AxiosError<any>) => {
    // Handle 401 Unauthorized - token might be expired or invalid
    if (error.response?.status === 401) {
      const message = error.response?.data?.message || 'Unauthenticated.';
      console.error('[API]', message);
      
      // If we have a token but got 401, it might be expired
      // Try to refresh user or clear auth state
      if (currentToken) {
        // Token might be invalid, but don't auto-logout here
        // Let the calling code handle it
      }
    }
    
    // Retry once on 419 by refreshing CSRF cookie
    if (error.response?.status === 419 && !csrfReady) {
      csrfReady = false;
    }
    
    const message = error.response?.data?.message || error.response?.data?.error || error.message;
    if (error.response?.status !== 401) {
      console.error('[API]', message);
    }
    return Promise.reject(error);
  }
);

// Ensure XSRF header is present on stateful, non-GET requests
api.interceptors.request.use((config) => {
  // Always ensure Authorization header is present if we have a stored token
  if (currentToken) {
    config.headers = { ...(config.headers || {}), Authorization: `Bearer ${currentToken}` } as any;
  }
  const method = (config.method || 'get').toLowerCase();
  if (config.withCredentials && method !== 'get') {
    const xsrf = getCookie('XSRF-TOKEN');
    if (xsrf && !config.headers?.['X-XSRF-TOKEN']) {
      config.headers = { ...(config.headers || {}), 'X-XSRF-TOKEN': xsrf } as any;
    }
  }
  return config;
});

export async function sanctum() {
  // Avoid duplicate calls if already have XSRF cookie
  if (csrfReady && getCookie('XSRF-TOKEN')) return;
  await api.get('/sanctum/csrf-cookie');
  csrfReady = true;
}

export async function login(payload: { email: string; password: string }): Promise<AuthResponse> {
  await sanctum();
  // ensure header is set when axios can't read cookie automatically
  const xsrf = getCookie('XSRF-TOKEN');
  const { data } = await api.post<AuthResponse>('/api/login', payload, xsrf ? { headers: { 'X-XSRF-TOKEN': xsrf } } : undefined);
  if (data.token) setAuthToken(data.token);
  return data;
}

export async function changePassword(payload: { current_password: string; password: string; password_confirmation: string }) {
  const { data } = await api.post<{ message: string; status: string }>('/api/change-password', payload);
  return data;
}

export async function register(payload: RegisterPayload) {
  await sanctum();
  const xsrf = getCookie('XSRF-TOKEN');
  const { data } = await api.post('/api/register', payload, xsrf ? { headers: { 'X-XSRF-TOKEN': xsrf } } : undefined);
  return data as { message: string; status?: string; must_change_password?: boolean; temporary_password_sent?: boolean };
}

export async function logout() {
  if (!currentToken) return;
  await api.post('/api/logout');
}

export async function fetchUser(): Promise<AuthUser | null> {
  try {
    const { data } = await api.get<AuthUser>('/api/user');
    return data;
  } catch (error: any) {
    if (error?.response?.status === 404) {
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
  const { data } = await api.post<{ transaction: Transaction; balance: number; message: string }>('/api/transaction/buy', payload);
  // Invalider les caches liés après une transaction
  cacheService.remove('portfolio');
  cacheService.remove('transaction_history_1_10');
  cacheService.remove('user_cryptos');
  return { transaction: data.transaction, balance: data.balance };
}

export async function sellCrypto(payload: SellPayload): Promise<{ transaction: Transaction; balance: number }> {
  const { data } = await api.post<{ transaction: Transaction; balance: number; message: string }>('/api/transaction/sell', payload);
  // Invalider les caches liés après une transaction
  cacheService.remove('portfolio');
  cacheService.remove('transaction_history_1_10');
  cacheService.remove('user_cryptos');
  return { transaction: data.transaction, balance: data.balance };
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

export async function getMarket(useCache: boolean = true) {
  const cacheKey = 'market';
  
  if (useCache) {
    return cacheService.preload(
      cacheKey,
      async () => {
        const { data } = await api.get<CryptoCurrency[]>('/api/market');
        return data;
      },
      { ttl: 30 * 1000 } // 30 secondes
    );
  }
  
  const { data } = await api.get<CryptoCurrency[]>('/api/market');
  cacheService.set(cacheKey, data, { ttl: 30 * 1000 });
  return data;
}

export async function getUserCryptos(useCache: boolean = true) {
  const cacheKey = 'user_cryptos';
  
  if (useCache) {
    return cacheService.preload(
      cacheKey,
      async () => {
        const { data } = await api.get<CryptoCurrency[]>('/api/user/cryptos');
        return data;
      },
      { ttl: 30 * 1000 } // 30 secondes
    );
  }
  
  const { data } = await api.get<CryptoCurrency[]>('/api/user/cryptos');
  cacheService.set(cacheKey, data, { ttl: 30 * 1000 });
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
  getUserCryptos,
  getMarketHistory,
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




