import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import api, { setAuthToken } from '@/services/api';
import type { AuthUser, LoginPayload } from '@/types';

const STORAGE_KEY = 'auth_state';

export const useAuthStore = defineStore('auth', () => {
  const user = ref<AuthUser | null>(null);
  const token = ref<string | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const hydrated = ref(false);

  const isAuthenticated = computed(() => Boolean(token.value && user.value));
  const needsPasswordChange = computed(() => Boolean(user.value?.must_change_password));

  function persist() {
    localStorage.setItem(
      STORAGE_KEY,
      JSON.stringify({
        token: token.value,
        user: user.value
      })
    );
  }

  function hydrate() {
    if (hydrated.value) return;
    const raw = localStorage.getItem(STORAGE_KEY);
    if (raw) {
      try {
        const parsed = JSON.parse(raw);
        token.value = parsed.token ?? null;
        user.value = parsed.user ?? null;
        if (token.value) setAuthToken(token.value);
      } catch (e) {
        console.warn('Failed to hydrate auth state', e);
      }
    }
    hydrated.value = true;
  }

  async function login(payload: LoginPayload) {
    loading.value = true;
    error.value = null;
    try {
      const data = await api.login(payload);
      token.value = data.token;
      user.value = data.user;
      setAuthToken(data.token);
      persist();
      return data;
    } catch (e: any) {
      error.value = e?.response?.data?.message || 'Erreur de connexion';
      throw e;
    } finally {
      loading.value = false;
    }
  }

  async function fetchCurrentUser() {
    hydrate();
    if (!token.value) return null;
    try {
      const fresh = await api.fetchUser();
      if (fresh) {
        user.value = fresh;
        persist();
      }
      return user.value;
    } catch (e) {
      await logout();
      return null;
    }
  }

  async function ensureUser() {
    if (user.value) return user.value;
    return fetchCurrentUser();
  }

  async function logout() {
    try {
      await api.logout();
    } catch (e) {
      // ignore network/logout errors to not block UI
    } finally {
      token.value = null;
      user.value = null;
      setAuthToken(null);
      localStorage.removeItem(STORAGE_KEY);
    }
  }

  return {
    user,
    token,
    loading,
    error,
    hydrated,
    isAuthenticated,
    needsPasswordChange,
    hydrate,
    login,
    logout,
    fetchCurrentUser,
    ensureUser
  };
});

