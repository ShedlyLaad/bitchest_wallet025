import axios, { AxiosError, type AxiosResponse } from 'axios';
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
  (error: AxiosError<any>) => {
    // Retry once on 419 by refreshing CSRF cookie
    if (error.response?.status === 419 && !csrfReady) {
      csrfReady = false;
    }
    const message = error.response?.data?.message || error.response?.data?.error || error.message;
    console.error('[API]', message);
    return Promise.reject(error);
  }
);

// Ensure XSRF header is present on stateful, non-GET requests
api.interceptors.request.use((config) => {
  // Ensure Authorization header is present if we have a stored token
  if (currentToken && !config.headers?.Authorization) {
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
export async function getPortfolio(): Promise<PortfolioResponse> {
  const { data } = await api.get<PortfolioResponse>('/api/portfolio');
  return data;
}

export async function buyCrypto(payload: BuyPayload): Promise<Transaction> {
  const { data } = await api.post<{ transaction: Transaction }>('/api/transaction/buy', payload);
  return data.transaction;
}

export async function sellCrypto(payload: SellPayload): Promise<Transaction> {
  const { data } = await api.post<{ transaction: Transaction }>('/api/transaction/sell', payload);
  return data.transaction;
}

export async function getTransactionHistory(params?: { page?: number; per_page?: number }): Promise<Paginated<Transaction>> {
  const { data } = await api.get<Paginated<Transaction>>('/api/transaction/history', { params });
  return data;
}

export async function getMarket() {
  const { data } = await api.get<CryptoCurrency[]>('/api/market');
  return data;
}

export async function getMarketHistory(symbol: string) {
  const { data } = await api.get<CryptoPricePoint[]>(`/api/market/history/${symbol}`);
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

export async function getAdminCryptoHistory(symbol: string) {
  const { data } = await api.get<CryptoPricePoint[]>(`/api/admin/cryptos/${symbol}/history`);
  return data;
}

export async function getAdminDashboard() {
  const { data } = await api.get('/api/admin/dashboard');
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
  getAdminDashboard
};

