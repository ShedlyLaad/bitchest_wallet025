<template>
  <div class="professional-trading-chart">
    <!-- Professional Header with Market Data -->
    <div class="chart-header bg-gray-800 border-b border-gray-700 px-4 py-3 sm:py-4">
      <!-- Top Row: Crypto Info and Price -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-3">
        <div class="flex items-center space-x-4">
          <div class="w-10 h-10 sm:w-12 sm:h-12 flex-shrink-0 bg-gray-700 rounded-lg flex items-center justify-center overflow-hidden border border-gray-600">
            <img 
              v-if="cryptoIcon" 
              :src="cryptoIcon" 
              :alt="cryptoName" 
              class="w-full h-full object-contain"
              @error="(e: any) => e.target.style.display = 'none'"
            />
            <span v-else class="text-white font-bold text-xs sm:text-sm">
              {{ symbol }}
            </span>
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h2 class="text-xl sm:text-2xl font-bold text-white">{{ cryptoName }}</h2>
              <span class="text-xs text-gray-500 font-mono">{{ symbol }}</span>
              <span class="text-xs text-gray-400 font-mono">{{ selectedTimeframe }}</span>
            </div>
            <div class="flex flex-wrap items-center gap-4 mt-1">
              <span class="text-lg font-semibold text-white">{{ formatPrice(currentPrice) }}</span>
              <span :class="[
                'flex items-center gap-1 text-sm font-medium px-2 py-0.5 rounded',
                priceChangePercent >= 0 
                  ? 'text-green-400 bg-green-400/10' 
                  : 'text-red-400 bg-red-400/10'
              ]">
                <component :is="priceChangePercent >= 0 ? TrendingUp : TrendingDown" class="h-3.5 w-3.5" />
                {{ priceChangePercent >= 0 ? '+' : '' }}{{ priceChangePercent.toFixed(2) }}%
              </span>
              <span class="text-xs text-gray-500">24h</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom Row: Market Stats -->
      <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs font-mono border-t border-gray-700/50 pt-3">
        <!-- Last Price -->
        <div class="flex items-center gap-1">
          <span class="text-gray-500">L:</span>
          <span class="text-white font-semibold">{{ formatPrice(currentPrice) }}</span>
        </div>
        
        <!-- Change -->
        <div class="flex items-center gap-1">
          <span class="text-gray-500">Change:</span>
          <span :class="priceChange >= 0 ? 'text-green-400' : 'text-red-400'">
            {{ priceChange >= 0 ? '+' : '' }}{{ formatPrice(priceChange) }}
          </span>
        </div>
        
        <!-- High -->
        <div class="flex items-center gap-1">
          <span class="text-gray-500">24h Hi:</span>
          <span class="text-green-400">{{ formatPrice(highPrice) }}</span>
        </div>
        
        <!-- Low -->
        <div class="flex items-center gap-1">
          <span class="text-gray-500">24h Lo:</span>
          <span class="text-red-400">{{ formatPrice(lowPrice) }}</span>
        </div>
        
        <!-- Volume -->
        <div class="flex items-center gap-1">
          <span class="text-gray-500">24h Vol:</span>
          <span class="text-white">{{ formatVolume(volume) }}</span>
        </div>
        
        <!-- Market Cap (if provided) -->
        <div v-if="marketCap" class="flex items-center gap-1">
          <span class="text-gray-500">Market Cap:</span>
          <span class="text-white">{{ formatVolume(marketCap) }}</span>
        </div>
      </div>
    </div>

    <!-- Chart Controls Bar -->
    <div class="chart-controls bg-gray-900/30 border-b border-gray-700/50 px-4 py-2 flex items-center justify-between">
      <!-- Timeframe Selector -->
      <div class="flex items-center gap-1">
        <button
          v-for="tf in timeframes"
          :key="tf.value"
          @click="selectedTimeframe = tf.value"
          :class="[
            'px-2 py-1 text-xs font-medium rounded transition-all',
            selectedTimeframe === tf.value
              ? 'bg-blue-600 text-white'
              : 'text-gray-400 hover:text-white hover:bg-gray-700/50'
          ]"
        >
          {{ tf.label }}
        </button>
      </div>

      <!-- Chart Type Selector -->
      <div class="flex items-center gap-1">
        <button
          @click="selectedChartType = 'line'"
          :class="[
            'px-3 py-1.5 text-xs font-medium rounded transition-all flex items-center gap-1.5 border',
            selectedChartType === 'line'
              ? 'bg-blue-600/20 text-blue-400 border-blue-500/50'
              : 'text-gray-400 hover:text-white hover:bg-gray-700/50 border-transparent'
          ]"
          title="Line Chart"
        >
          <TrendingUp class="h-3.5 w-3.5" />
          Line
        </button>
        <button
          @click="selectedChartType = 'area'"
          :class="[
            'px-3 py-1.5 text-xs font-medium rounded transition-all flex items-center gap-1.5 border',
            selectedChartType === 'area'
              ? 'bg-green-600/20 text-green-400 border-green-500/50'
              : 'text-gray-400 hover:text-white hover:bg-gray-700/50 border-transparent'
          ]"
          title="Area Chart"
        >
          <Activity class="h-3.5 w-3.5" />
          Area
        </button>
      </div>
    </div>

    <!-- Chart Container with Watermark -->
    <div class="chart-container relative" :style="{ height: typeof height === 'number' ? `${height}px` : height }">
      <!-- Watermark -->
      <div class="absolute inset-0 pointer-events-none z-10 flex items-center justify-center">
        <div class="text-center opacity-10">
          <div class="text-4xl font-bold text-white mb-1">{{ symbol }}, {{ selectedTimeframe }}</div>
          <div class="text-2xl text-gray-400">{{ cryptoName }}</div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center bg-gray-900/80 z-20">
        <div class="text-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto mb-2"></div>
          <p class="text-gray-400 text-sm">Loading chart data...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="absolute inset-0 flex items-center justify-center bg-gray-900/80 z-20">
        <div class="text-center text-red-400">
          <p class="text-sm">{{ error }}</p>
        </div>
      </div>

      <!-- Chart -->
      <ApexChart
        v-else-if="chartOptions && chartSeries && chartSeries.length > 0 && chartSeries[0].data && chartSeries[0].data.length > 0"
        :key="`chart-${selectedTimeframe}-${selectedChartType}-${symbol}-${chartSeries[0].data.length}-${currentPrice}`"
        :options="chartOptions"
        :series="chartSeries"
        :type="selectedChartType"
        :height="typeof height === 'number' ? height : undefined"
        width="100%"
        class="relative z-0 chart-wrapper"
      />
      <!-- No data message -->
      <div v-else-if="!isLoading && !error && (!chartSeries || chartSeries.length === 0 || !chartSeries[0].data || chartSeries[0].data.length === 0)" class="absolute inset-0 flex items-center justify-center bg-gray-900/80 z-20">
        <div class="text-center text-gray-400">
          <p class="text-sm font-medium mb-1">No chart data available</p>
          <p class="text-xs text-gray-500">Loading historical data from Coinbase...</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import ApexChart from 'vue3-apexcharts';
import type { ApexOptions } from 'apexcharts';
import { TrendingUp, TrendingDown, Activity } from 'lucide-vue-next';
import type { CryptoPricePoint } from '@/types';

interface Props {
  symbol: string;
  cryptoName?: string;
  priceData?: number[];
  priceDataWithDates?: CryptoPricePoint[];
  currentPrice?: number;
  change24h?: number;
  height?: number | string;
  currency?: 'USD' | 'EUR';
  cryptoIcon?: string;
  marketCap?: number;
  timeframe?: string;
}

const emit = defineEmits<{
  timeframeChanged: [timeframe: string];
}>();

const props = withDefaults(defineProps<Props>(), {
  height: 500,
  currency: 'EUR',
  currentPrice: 0,
  change24h: 0
});

const timeframes = [
  { label: '1d', value: '1d' },
  { label: '7d', value: '7d' },
  { label: '30d', value: '30d' },
  { label: '90d', value: '90d' }
];

const selectedTimeframe = ref(props.timeframe || '30d');
const selectedChartType = ref<'line' | 'area'>('line');
const isLoading = ref(false);
const error = ref('');

// Calculate market data from price history
const marketData = computed(() => {
  // Use priceDataWithDates if available
  if (props.priceDataWithDates && props.priceDataWithDates.length > 0) {
    const prices = props.priceDataWithDates.map(p => Number(p.price));
    const open = prices[0] || props.currentPrice;
    const close = prices[prices.length - 1] || props.currentPrice;
    const high = Math.max(...prices, props.currentPrice);
    const low = Math.min(...prices, props.currentPrice);
    const volume = prices.length * 1000000;
    return { open, high, low, close, volume };
  }
  
  // Fallback to priceData array
  if (props.priceData && props.priceData.length > 0) {
    const prices = props.priceData.map(p => Number(p));
    const open = prices[0] || props.currentPrice;
    const close = prices[prices.length - 1] || props.currentPrice;
    const high = Math.max(...prices, props.currentPrice);
    const low = Math.min(...prices, props.currentPrice);
    const volume = prices.length * 1000000;
    return { open, high, low, close, volume };
  }

  return {
    open: props.currentPrice,
    high: props.currentPrice,
    low: props.currentPrice,
    close: props.currentPrice,
    volume: 0
  };
});

const currentPrice = computed(() => props.currentPrice || marketData.value.close);
const openPrice = computed(() => marketData.value.open);
const highPrice = computed(() => marketData.value.high);
const lowPrice = computed(() => marketData.value.low);
const volume = computed(() => marketData.value.volume);

// Calculate price change - Utiliser directement change24h de Coinbase (déjà validé côté backend)
// Le backend limite déjà change24h à -50% à +200%, donc on peut l'utiliser directement
const priceChange = computed(() => {
  // Utiliser change24h directement depuis Coinbase (déjà validé backend)
  if (props.change24h !== undefined && props.change24h !== null && !isNaN(props.change24h)) {
    return (currentPrice.value * props.change24h) / 100;
  }
  // Fallback: calculer depuis l'historique si change24h n'est pas disponible
  return currentPrice.value - openPrice.value;
});

const priceChangePercent = computed(() => {
  // Utiliser change24h directement depuis Coinbase (déjà validé backend)
  if (props.change24h !== undefined && props.change24h !== null && !isNaN(props.change24h)) {
    return props.change24h;
  }
  // Fallback: calculer depuis l'historique si change24h n'est pas disponible
  if (openPrice.value === 0) return 0;
  return ((currentPrice.value - openPrice.value) / openPrice.value) * 100;
});

// Format date label based on timeframe
const formatDateLabel = (date: Date, timeframe: string): string => {
  if (timeframe === '90d' || timeframe === '30d') {
    // For longer periods, show month and day
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
  } else if (timeframe === '7d') {
    // For 7 days, show day of week and date
    return date.toLocaleDateString('en-US', { weekday: 'short', day: 'numeric' });
  } else if (timeframe === '1d') {
    // For 1 day, show time
    return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
  }
  // Default: show date
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

// Construire les séries depuis l'historique Coinbase - Simple et direct
const buildSeriesFromHistory = (): Array<{ x: number; y: number }> => {
  if (!props.priceDataWithDates || props.priceDataWithDates.length === 0) return [];

  const now = new Date();
  const timeMap: Record<string, number> = {
    '1d': 86400000,
    '7d': 604800000,
    '30d': 2592000000,
    '90d': 7776000000
  };
  const timeframeMs = timeMap[selectedTimeframe.value] || 2592000000;
  const cutoffTime = now.getTime() - timeframeMs;

  // Traiter les données Coinbase : trier, filtrer par timeframe, valider
  const validPoints: Array<{ x: number; y: number }> = [];

  [...props.priceDataWithDates]
    .sort((a, b) => new Date(a.recorded_at).getTime() - new Date(b.recorded_at).getTime())
    .forEach((point) => {
      try {
        const date = new Date(point.recorded_at);
        const ts = date.getTime();
        const price = Number(point.price);

        // Validation stricte : rejeter les données invalides
        if (isNaN(ts) || ts <= 0) return;
        if (isNaN(price) || price <= 0) return;
        
        // Filtrer par timeframe - être permissif si peu de données
        const isWithinTimeframe = ts >= cutoffTime;
        
        // Si on a peu de données dans le timeframe strict, accepter toutes les données récentes (90 jours)
        if (!isWithinTimeframe) {
          const isRecent = ts >= (now.getTime() - (90 * 86400000));
          if (!isRecent) {
            return; // Rejeter les données trop anciennes (> 90 jours)
          }
          // Accepter les données récentes même si elles sont en dehors du timeframe strict
          // Cela permet d'afficher quelque chose même si les données ne sont pas parfaitement alignées
        }

        validPoints.push({ x: ts, y: price });
      } catch (e) {
        // Ignorer les points avec erreur de parsing
        console.warn('Invalid data point:', point, e);
      }
    });

  // Si on a moins de 2 points, utiliser toutes les données disponibles (jusqu'à 90 jours)
  if (validPoints.length < 2) {
    const allPoints: Array<{ x: number; y: number }> = [];
    const maxCutoff = now.getTime() - (90 * 86400000);
    
    [...props.priceDataWithDates]
      .sort((a, b) => new Date(a.recorded_at).getTime() - new Date(b.recorded_at).getTime())
      .forEach((point) => {
        try {
          const date = new Date(point.recorded_at);
          const ts = date.getTime();
          const price = Number(point.price);
          if (isNaN(ts) || ts <= 0) return;
          if (isNaN(price) || price <= 0) return;
          if (ts < maxCutoff) return;
          allPoints.push({ x: ts, y: price });
        } catch (e) {
          // Ignorer
        }
      });
    
    if (allPoints.length >= 2) {
      // Dédupliquer et retourner
      const byTs = new Map<number, number>();
      allPoints.forEach(({ x, y }) => {
        byTs.set(x, y);
      });
      return Array.from(byTs.entries())
        .sort((a, b) => a[0] - b[0])
        .map(([x, y]) => ({ x, y }));
    }
  }

  // Dédupliquer par timestamp (garder le dernier prix pour chaque timestamp)
  const byTs = new Map<number, number>();
  validPoints.forEach(({ x, y }) => {
    byTs.set(x, y);
  });

  return Array.from(byTs.entries())
    .sort((a, b) => a[0] - b[0])
    .map(([x, y]) => ({ x, y }));
};

const buildSeriesFromArray = (): Array<{ x: number; y: number }> => {
  if (!props.priceData || props.priceData.length === 0) return [];
  const now = new Date();
  const timeMap: Record<string, number> = {
    '1d': 86400000,
    '7d': 604800000,
    '30d': 2592000000,
    '90d': 7776000000
  };
  const totalTime = timeMap[selectedTimeframe.value] || 2592000000;
  const interval = totalTime / props.priceData.length;
  const startTime = now.getTime() - totalTime;

  return props.priceData
    .map((price, index) => {
      const priceNum = Number(price);
      if (isNaN(priceNum) || priceNum <= 0) return null;
      return { x: startTime + index * interval, y: priceNum };
    })
    .filter((p): p is { x: number; y: number } => p !== null);
};

// Construire les séries pour le chart - Utiliser uniquement les données réelles Coinbase
const chartSeries = computed(() => {
  // Priorité 1: Utiliser priceDataWithDates (données Coinbase depuis l'API)
  let data = buildSeriesFromHistory();

  // Priorité 2: Fallback vers priceData si disponible (moins idéal mais acceptable)
  if (data.length < 2) {
    const fallbackFromArray = buildSeriesFromArray();
    if (fallbackFromArray.length >= 2) {
      data = fallbackFromArray;
    }
  }

  // Si aucune donnée valide, retourner vide (pas de données synthétiques)
  // Cela permet de voir clairement quand les données ne sont pas disponibles
  if (data.length === 0) {
    return [];
  }

  // Retourner les données réelles uniquement
  return [{
    name: props.symbol,
    data: data
  }];
});

const formatPrice = (value: number): string => {
  if (props.currency === 'EUR') {
    return new Intl.NumberFormat('en-GB', {
      style: 'currency',
      currency: 'EUR',
      minimumFractionDigits: 2,
      maximumFractionDigits: 8
    }).format(value);
  }
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 2,
    maximumFractionDigits: 8
  }).format(value);
};

const formatVolume = (value: number): string => {
  if (value >= 1000000) {
    return `${(value / 1000000).toFixed(2)}M`;
  } else if (value >= 1000) {
    return `${(value / 1000).toFixed(2)}K`;
  }
  return value.toFixed(0);
};

const chartOptions = computed<ApexOptions>(() => {
  const baseOptions: ApexOptions = {
    chart: {
      height: typeof props.height === 'number' ? props.height : undefined,
      background: 'transparent', // Professional dark background
      id: `chart-${props.symbol}-${selectedTimeframe.value}`,
      toolbar: {
        show: true,
        tools: {
          download: false,
          selection: false,
          zoom: false,
          zoomin: true,
          zoomout: true,
          pan: false,
          reset: false
        },
        offsetX: 0,
        offsetY: 0
      },
      zoom: {
        enabled: true,
        type: 'x',
        autoScaleYaxis: true
      },
      animations: {
        enabled: true,
        speed: 800,
        animateGradually: {
          enabled: true,
          delay: 150
        },
        dynamicAnimation: {
          enabled: true,
          speed: 350
        }
      }
    },
    theme: {
      mode: 'dark'
    },
    xaxis: {
      type: 'datetime',
      labels: {
        style: {
          colors: '#9ca3af',
          fontSize: '11px',
          fontFamily: 'monospace',
          fontWeight: 400
        },
        rotate: selectedTimeframe.value === '1d' ? 0 : -45,
        rotateAlways: false,
        hideOverlappingLabels: true,
        showDuplicates: false,
        offsetY: 5,
        datetimeUTC: false,
        format: selectedTimeframe.value === '1d' 
          ? 'HH:mm' 
          : selectedTimeframe.value === '7d'
          ? 'ddd dd'
          : selectedTimeframe.value === '30d' || selectedTimeframe.value === '90d'
          ? 'MMM dd'
          : 'MMM dd',
        datetimeFormatter: {
          year: 'yyyy',
          month: 'MMM \'yy',
          day: 'dd MMM',
          hour: 'HH:mm'
        }
      },
      axisBorder: {
        show: true,
        height: 1
      },
      axisTicks: {
        show: false
      },
      crosshairs: {
        show: true,
        width: 1,
        position: 'back',
        stroke: {
          color: '#60a5fa',
          width: 1,
          dashArray: 4
        },
        opacity: 0.4
      },
      tooltip: {
        enabled: false
      }
    },
    yaxis: {
      labels: {
        style: {
          colors: '#9ca3af',
          fontSize: '11px',
          fontFamily: 'monospace',
          fontWeight: 400
        },
        formatter: (val: number) => {
          // Format with appropriate decimal places based on price
          if (val >= 1000) {
            return val.toFixed(2);
          } else if (val >= 1) {
            return val.toFixed(4);
          } else {
            return val.toFixed(8);
          }
        },
        offsetX: -5
      },
      tooltip: {
        enabled: true
      },
      crosshairs: {
        show: true,
        position: 'back',
        stroke: {
          color: '#60a5fa',
          width: 1,
          dashArray: 4
        }
      },
      axisBorder: {
        show: true,
        width: 1
      }
    },
    grid: {
      borderColor: '#374151',
      strokeDashArray: 0,
      xaxis: {
        lines: {
          show: true
        }
      },
      yaxis: {
        lines: {
          show: true
        }
      },
      padding: {
        top: 10,
        right: 10,
        bottom: 10,
        left: 10
      }
    },
    tooltip: {
      enabled: true,
      theme: 'dark',
      shared: false,
      intersect: false,
      style: {
        fontSize: '11px',
        fontFamily: 'monospace'
      },
      custom: ({ seriesIndex, dataPointIndex, w }) => {
        const series = w.globals.initialSeries[seriesIndex];
        const dataPoint = series.data[dataPointIndex];
        const timestamp = typeof dataPoint.x === 'number' ? dataPoint.x : new Date(dataPoint.x).getTime();
        const date = new Date(timestamp);
        const value = typeof dataPoint.y === 'number' ? dataPoint.y : Number(dataPoint.y);
        
        const dateLabel = formatDateLabel(date, selectedTimeframe.value);
        
        return `
          <div class="px-2 py-1.5 bg-gray-800 border border-gray-600 rounded text-xs">
            <div class="text-gray-400 mb-1">${dateLabel}</div>
            <div class="text-white font-semibold">${formatPrice(value)}</div>
          </div>
        `;
      },
      x: {
        format: 'dd MMM yyyy HH:mm'
      }
    },
    stroke: {
      width: selectedChartType.value === 'line' ? 3 : 2,
      curve: 'smooth',
      colors: selectedChartType.value === 'line' ? ['#60a5fa'] : ['#10b981'],
      lineCap: 'round',
      show: true,
      dashArray: 0
    },
    markers: {
      size: 0,
      hover: {
        size: 5
      }
    },
    fill: {
      type: selectedChartType.value === 'area' ? 'gradient' : 'solid',
      gradient: selectedChartType.value === 'area' ? {
        type: 'vertical',
        shadeIntensity: 1,
        gradientToColors: ['#10b981'],
        inverseColors: false,
        opacityFrom: 0.4,
        opacityTo: 0.1,
        stops: [0, 50, 100]
      } : undefined,
      colors: selectedChartType.value === 'area' ? ['#10b981'] : ['transparent'],
      opacity: selectedChartType.value === 'area' ? 1 : 0
    },
    dataLabels: {
      enabled: false
    },
    noData: {
      text: 'No data available',
      align: 'center',
      verticalAlign: 'middle',
      style: {
        color: '#6b7280',
        fontSize: '14px'
      }
    }
  };
  
  return baseOptions;
});

// Watch for timeframe changes
watch(selectedTimeframe, (newTimeframe) => {
  emit('timeframeChanged', newTimeframe);
});

// Watch for prop changes
watch(() => props.timeframe, (newTimeframe) => {
  if (newTimeframe && newTimeframe !== selectedTimeframe.value) {
    selectedTimeframe.value = newTimeframe;
  }
});

// Watch for data changes to ensure chart updates
watch(() => [props.priceDataWithDates, props.priceData, props.currentPrice, props.symbol], () => {
  // Force chart re-render when data changes
  // This ensures the chart updates when switching between cryptos or when new data arrives
}, { deep: true, immediate: false });

// Watch chart type changes
watch(selectedChartType, () => {
  // Chart will update automatically via :key binding
});

</script>

<style scoped>
.professional-trading-chart {
  @apply w-full bg-gray-950 border border-gray-800 rounded-lg overflow-hidden;
}

.chart-header {
  @apply border-b border-gray-700;
}

.chart-controls {
  @apply border-b border-gray-700/50;
}

.chart-container {
  @apply relative;
  background-color: #0f172a; /* Fond très foncé professionnel (slate-950) */
  background-image: 
    linear-gradient(rgba(156, 163, 175, 0.05) 1px, transparent 1px),
    linear-gradient(90deg, rgba(156, 163, 175, 0.05) 1px, transparent 1px);
  background-size: 50px 50px; /* Grille fine et discrète */
}

/* Assurer la visibilité des lignes du chart */
.chart-wrapper :deep(.apexcharts-line-series path) {
  stroke-width: 3px !important;
  stroke: #60a5fa !important;
  fill: none !important;
  filter: drop-shadow(0 0 2px rgba(96, 165, 250, 0.5));
}

.chart-wrapper :deep(.apexcharts-area-series path) {
  stroke-width: 2px !important;
  stroke: #10b981 !important;
  fill: url(#gradient) !important;
}

.chart-wrapper :deep(.apexcharts-line-series .apexcharts-line) {
  stroke-width: 3px !important;
  fill: none !important;
}

.chart-wrapper :deep(.apexcharts-area-series .apexcharts-area) {
  stroke-width: 2px !important;
  stroke: #10b981 !important;
}

/* Ensure gradients are visible for area charts */
.chart-wrapper :deep(.apexcharts-area-series .apexcharts-area) {
  opacity: 1 !important;
}

/* Ensure tooltip is visible */
.chart-wrapper :deep(.apexcharts-tooltip) {
  background: rgba(31, 41, 55, 0.95) !important;
  border: 1px solid rgba(75, 85, 99, 0.5) !important;
  border-radius: 6px !important;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3) !important;
}
</style>
