<template>
  <div class="min-h-screen flex items-center justify-center bg-slate-900 px-4">
    <div class="w-full max-w-lg bg-slate-800 border border-slate-700 rounded-2xl p-8 shadow-xl space-y-6">
      <div class="space-y-2 text-center">
        <h1 class="text-2xl font-semibold text-white">Mettre à jour votre mot de passe</h1>
        <p class="text-slate-400 text-sm">Ce compte nécessite un nouveau mot de passe avant validation par un administrateur.</p>
      </div>

      <div v-if="successMessage" class="p-3 rounded-lg bg-emerald-900/50 border border-emerald-700 text-emerald-200 text-sm">
        {{ successMessage }}
      </div>
      <div v-if="errorMessage" class="p-3 rounded-lg bg-red-900/50 border border-red-700 text-red-200 text-sm">
        {{ errorMessage }}
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div class="space-y-2">
          <label class="text-sm text-slate-300">Mot de passe temporaire</label>
          <input
            v-model="form.current_password"
            type="password"
            required
            class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div class="space-y-2">
          <label class="text-sm text-slate-300">Nouveau mot de passe</label>
          <input
            v-model="form.password"
            type="password"
            required
            minlength="8"
            class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div class="space-y-2">
          <label class="text-sm text-slate-300">Confirmer le mot de passe</label>
          <input
            v-model="form.password_confirmation"
            type="password"
            required
            minlength="8"
            class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full py-2 rounded-lg text-white font-medium transition disabled:opacity-60 disabled:cursor-not-allowed"
          :style="{ backgroundColor: 'var(--blue)' }"
        >
          {{ loading ? 'Mise à jour...' : 'Changer mon mot de passe' }}
        </button>
      </form>

      <p class="text-center text-slate-400 text-xs">
        Après changement, votre compte sera en attente de validation par un administrateur.
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { changePassword } from '@/services/api';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const auth = useAuthStore();

const form = reactive({
  current_password: '',
  password: '',
  password_confirmation: ''
});

const loading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

async function handleSubmit() {
  loading.value = true;
  successMessage.value = '';
  errorMessage.value = '';
  try {
    const { message } = await changePassword(form);
    successMessage.value = message || 'Mot de passe mis à jour.';
    await auth.logout();
    setTimeout(() => {
      router.push({ name: 'Signin', query: { reason: 'pending_validation' } });
    }, 1200);
  } catch (e: any) {
    errorMessage.value = e?.response?.data?.message || 'Impossible de changer le mot de passe';
  } finally {
    loading.value = false;
  }
}
</script>

