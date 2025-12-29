<template>
  <div class="min-h-screen bg-gray-900 text-white">
    <div class="max-w-7xl mx-auto p-3 sm:p-6 space-y-4 sm:space-y-8">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-3 mb-2">
            <Wallet class="h-8 w-8 text-blue-400" />
            <h1 class="text-2xl sm:text-3xl font-bold">Portfolio</h1>
          </div>
          <p class="text-gray-400 text-sm sm:text-base">Gérez vos avoirs en cryptomonnaies</p>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        <div class="bg-gray-800 rounded-xl p-4 sm:p-6 border border-gray-700">
          <div class="text-gray-400 text-sm mb-2">Valeur totale du portfolio</div>
          <div class="text-2xl sm:text-3xl font-bold">{{ formatEUR(totalPortfolioValue) }}</div>
        </div>

        <div class="bg-gray-800 rounded-xl p-4 sm:p-6 border border-gray-700">
          <div class="text-gray-400 text-sm mb-2">P/L non réalisé</div>
          <div :class="`text-2xl sm:text-3xl font-bold ${totalPL >= 0 ? 'PnL--pos' : 'PnL--neg'}`">
            <component :is="totalPL >= 0 ? TrendingUp : TrendingDown" class="h-5 w-5 inline mr-1" />
            {{ formatEUR(totalPL) }}
          </div>
        </div>

        <div class="bg-gray-800 rounded-xl p-4 sm:p-6 border border-gray-700">
          <div class="text-gray-400 text-sm mb-2">Nombre d'actifs</div>
          <div class="text-2xl sm:text-3xl font-bold text-app-secondary">{{ portfolioItems.length }}</div>
        </div>
      </div>

      <!-- Portfolio Table -->
      <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-700">
          <h2 class="text-lg sm:text-xl font-semibold">Détails des actifs</h2>
          <p class="text-sm text-gray-400 mt-1">Cliquez sur une ligne pour voir les détails d'achat</p>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-gray-700">
                <th class="text-left px-4 sm:px-6 py-4 text-sm font-semibold text-gray-300">Crypto</th>
                <th class="text-right px-4 sm:px-6 py-4 text-sm font-semibold text-gray-300">Quantity</th>
                <th class="text-right px-4 sm:px-6 py-4 text-sm font-semibold text-gray-300">Avg buy price (€)</th>
                <th class="text-right px-4 sm:px-6 py-4 text-sm font-semibold text-gray-300">Current price (€)</th>
                <th class="text-right px-4 sm:px-6 py-4 text-sm font-semibold text-gray-300">Unrealized P/L (€)</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-700">
              <tr
                v-for="(item, index) in portfolioItems"
                :key="item.crypto.id"
                @click="handleRowClick(item)"
                :class="[
                  'hover:bg-gray-700/30 transition-colors cursor-pointer',
                  index % 2 === 0 ? 'bg-gray-800' : 'bg-gray-800/50'
                ]"
              >
                <td class="px-4 sm:px-6 py-4">
                  <div class="flex items-center gap-3">
                    <template v-if="isString(item.crypto.icon)">
                      <img
                        :src="item.crypto.icon"
                        :alt="item.crypto.name"
                        class="w-8 h-8 rounded-full object-cover"
                        @error="hideBrokenImage($event)"
                      />
                    </template>
                    <template v-else>
                      <div class="w-8 h-8 flex items-center justify-center">
                        <!-- if icon is a component / element -->
                        <component :is="item.crypto.icon" />
                      </div>
                    </template>

                    <div>
                      <div class="font-semibold">{{ item.crypto.name }}</div>
                      <div class="text-sm text-gray-400">{{ item.crypto.symbol }}</div>
                    </div>
                  </div>
                </td>

                <td class="px-4 sm:px-6 py-4 text-right font-medium">
                  {{ formatNumber(item.totalQuantity) }}
                </td>

                <td class="px-4 sm:px-6 py-4 text-right">
                  {{ formatEUR(item.avgPrice) }}
                </td>

                <td class="px-4 sm:px-6 py-4 text-right">
                  {{ formatEUR(item.currentPrice) }}
                </td>

                <td :class="`px-4 sm:px-6 py-4 text-right font-semibold ${item.pl >= 0 ? 'PnL--pos' : 'PnL--neg'}`">
                  <div class="flex items-center justify-end gap-1">
                    <component :is="item.pl >= 0 ? TrendingUp : TrendingDown" class="h-4 w-4" />
                    {{ formatEUR(item.pl) }}
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal / Drawer for Purchase Details -->
      <div v-if="isModalOpen && selectedCrypto" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" @click="closeModal">
        <div
          class="bg-gray-800 rounded-xl border border-gray-700 w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col"
          @click.stop
        >
          <!-- Modal Header -->
          <div class="flex items-center justify-between p-4 sm:p-6 border-b border-gray-700">
            <div class="flex items-center gap-3">
              <Wallet class="h-6 w-6 text-blue-400" />
              <h2 class="text-xl sm:text-2xl font-bold">Détails des achats</h2>
            </div>
            <button @click="closeModal" class="p-2 hover:bg-gray-700 rounded-lg transition-colors" aria-label="Fermer">
              <X class="h-5 w-5" />
            </button>
          </div>

          <!-- Modal Content -->
          <div class="flex-1 overflow-y-auto p-4 sm:p-6">
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="border-b border-gray-700">
                    <th class="text-left px-4 py-3 text-sm font-semibold text-gray-300">Date</th>
                    <th class="text-right px-4 py-3 text-sm font-semibold text-gray-300">Quantity</th>
                    <th class="text-right px-4 py-3 text-sm font-semibold text-gray-300">Price (€ per unit)</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                  <tr
                    v-for="(purchase, idx) in selectedCrypto.purchases"
                    :key="idx"
                    :class="idx % 2 === 0 ? 'bg-gray-800' : 'bg-gray-800/50'"
                  >
                    <td class="px-4 py-3">
                      {{ formatDate(purchase.date) }}
                    </td>
                    <td class="px-4 py-3 text-right">
                      {{ formatNumber(purchase.quantity) }}
                    </td>
                    <td class="px-4 py-3 text-right">
                      {{ formatEUR(purchase.price) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer - Full Width -->
    <FooterSection />
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Wallet, X, TrendingUp, TrendingDown } from 'lucide-vue-next';

import { cryptocurrencies, portfolioData } from '../data/mockData';
import { formatEUR } from '../utils/formatEUR';
import { avgBuyPrice, unrealizedPL } from '../utils/finance';
import FooterSection from '../components/sectionsLanding/FooterSection.vue';

interface Purchase {
  date: string;
  quantity: number;
  price: number;
}

interface PortfolioItemRaw {
  cryptoId: string;
  purchases: Purchase[];
}

const selectedCrypto = ref<PortfolioItemRaw | null>(null);
const isModalOpen = ref(false);

function isString(v: unknown): v is string {
  return typeof v === 'string';
}

function hideBrokenImage(e: Event) {
  const target = e.target as HTMLImageElement;
  if (target) target.style.display = 'none';
}

const portfolioItems = computed(() => {
  if (!portfolioData || portfolioData.length === 0) return [];

  return portfolioData
    .map((item) => {
      const crypto = cryptocurrencies.find((c) => c.id === item.cryptoId);
      if (!crypto) {
        console.warn(`Cryptocurrency not found for id: ${item.cryptoId}`);
        return null;
      }
      const purchasesForCalc = item.purchases.map((p) => ({ qty: p.quantity, price: p.price }));
      const totalQuantity = item.purchases.reduce((s, p) => s + p.quantity, 0);
      const avgPrice = avgBuyPrice(purchasesForCalc);
      const currentPrice = crypto.price;
      const pl = unrealizedPL(purchasesForCalc, currentPrice);

      return {
        crypto,
        totalQuantity,
        avgPrice,
        currentPrice,
        pl,
        purchases: item.purchases
      };
    })
    .filter((x): x is NonNullable<typeof x> => x !== null);
});

const handleRowClick = (item: (typeof portfolioItems.value)[0]) => {
  const portfolioItem = portfolioData.find((p) => p.cryptoId === item.crypto.id) || null;
  if (portfolioItem) {
    selectedCrypto.value = portfolioItem;
    isModalOpen.value = true;
  }
};

const closeModal = () => {
  isModalOpen.value = false;
  selectedCrypto.value = null;
};

const totalPortfolioValue = computed(() =>
  portfolioItems.value.reduce((sum, item) => sum + item.currentPrice * item.totalQuantity, 0)
);

const totalPL = computed(() => portfolioItems.value.reduce((sum, item) => sum + item.pl, 0));

function formatNumber(value: number) {
  return value.toLocaleString('en-GB', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 8
  });
}

function formatDate(d: string) {
  return new Date(d).toLocaleDateString('en-GB', { year: 'numeric', month: 'short', day: 'numeric' });
}

</script>

<style scoped>
/* Minimal local styles; main look controlled by Tailwind utilities */
</style>