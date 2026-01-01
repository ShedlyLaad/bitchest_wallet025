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
        v-else-if="chartOptions && chartSeries"
        :options="chartOptions"
        :series="chartSeries"
        :type="selectedChartType"
        :height="typeof height === 'number' ? height : undefined"
        width="100%"
        class="relative z-0"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import ApexChart from 'vue3-apexcharts';
import type { ApexOptions } from 'apexcharts';
import { TrendingUp, TrendingDown, Activity } from 'lucide-vue-next';

interface Props {
  symbol: string;
  cryptoName?: string;
  priceData?: number[];
  currentPrice?: number;
  change24h?: number;
  height?: number | string;
  currency?: 'USD' | 'EUR';
  cryptoIcon?: string;
  marketCap?: number;
}

const props = withDefaults(defineProps<Props>(), {
  height: 500,
  currency: 'EUR',
  currentPrice: 0,
  change24h: 0
});

const timeframes = [
  { label: '1h', value: '1h' },
  { label: '4h', value: '4h' },
  { label: '1d', value: '1d' },
  { label: '7d', value: '7d' },
  { label: '30d', value: '30d' },
  { label: '60d', value: '60d' }
];

const selectedTimeframe = ref('1h');
const selectedChartType = ref<'line' | 'area'>('line');
const isLoading = ref(false);
const error = ref('');

// Calculate market data from price history
const marketData = computed(() => {
  if (!props.priceData || props.priceData.length === 0) {
    return {
      open: props.currentPrice,
      high: props.currentPrice,
      low: props.currentPrice,
      close: props.currentPrice,
      volume: 0
    };
  }

  const prices = props.priceData;
  const open = prices[0] || props.currentPrice;
  const close = prices[prices.length - 1] || props.currentPrice;
  const high = Math.max(...prices, props.currentPrice);
  const low = Math.min(...prices, props.currentPrice);
  
  // Simulate volume based on price movement
  const volume = prices.length * 1000000 * (1 + Math.random() * 2);

  return { open, high, low, close, volume };
});

const currentPrice = computed(() => props.currentPrice || marketData.value.close);
const openPrice = computed(() => marketData.value.open);
const highPrice = computed(() => marketData.value.high);
const lowPrice = computed(() => marketData.value.low);
const volume = computed(() => marketData.value.volume);

// Calculate price change
const priceChange = computed(() => {
  if (props.change24h !== undefined && props.change24h !== 0) {
    return (currentPrice.value * props.change24h) / 100;
  }
  return currentPrice.value - openPrice.value;
});

const priceChangePercent = computed(() => {
  if (props.change24h !== undefined && props.change24h !== 0) {
    return props.change24h;
  }
  if (openPrice.value === 0) return 0;
  return ((currentPrice.value - openPrice.value) / openPrice.value) * 100;
});

// Generate labels for chart based on timeframe
const generateLabels = (count: number, timeframe: string): string[] => {
  const labels: string[] = [];
  const now = new Date();
  
  const timeMap: Record<string, number> = {
    '1h': 3600000,
    '4h': 14400000,
    '1d': 86400000,
    '7d': 604800000,
    '30d': 2592000000,
    '60d': 5184000000
  };
  
  const interval = timeMap[timeframe] || 3600000; // Default to 1h
  
  // Start from the past and go to now
  for (let i = count - 1; i >= 0; i--) {
    const date = new Date(now.getTime() - (i * interval));
    let label = '';
    
    if (timeframe === '60d' || timeframe === '30d' || timeframe === '7d') {
      label = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    } else if (timeframe === '1d') {
      label = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    } else if (timeframe === '4h' || timeframe === '1h') {
      // For 1h and 4h, use time format (HH:MM)
      label = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
    } else {
      label = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
    }
    
    labels.push(label);
  }
  
  return labels;
};

const chartSeries = computed(() => {
  // For line and area charts, use price data directly
  if (!props.priceData || props.priceData.length === 0) {
    // Generate fallback data based on timeframe
    const fallbackPrice = props.currentPrice || 100;
    const timeframePoints: Record<string, number> = {
      '1h': 60,
      '4h': 48,
      '1d': 24,
      '7d': 84,
      '30d': 90,
      '60d': 120
    };
    const pointCount = timeframePoints[selectedTimeframe.value] || 50;
    const fallbackData = Array.from({ length: pointCount }, (_, i) => {
      const variation = Math.sin(i / pointCount * Math.PI * 4) * 0.05;
      return fallbackPrice * (1 + variation);
    });
    const labels = generateLabels(fallbackData.length, selectedTimeframe.value);
    return [{
      name: props.symbol,
      data: fallbackData.map((price, index) => ({
        x: labels[index] || index.toString(),
        y: price
      }))
    }];
  }
  
  // Use actual price data
  const labels = generateLabels(props.priceData.length, selectedTimeframe.value);
  return [{
    name: props.symbol,
    data: props.priceData.map((price, index) => ({
      x: labels[index] || index.toString(),
      y: price
    }))
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
      type: selectedChartType.value,
      height: typeof props.height === 'number' ? props.height : undefined,
      background: 'transparent', // Professional dark background
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
      type: 'category',
      labels: {
        style: {
          colors: '#6b7280',
          fontSize: '10px',
          fontFamily: 'monospace',
          fontWeight: 400
        },
        rotate: selectedTimeframe.value === '1h' || selectedTimeframe.value === '4h' ? 0 : -45,
        rotateAlways: false,
        hideOverlappingLabels: true,
        showDuplicates: false,
        offsetY: 5
      },
      axisBorder: {
        show: true,
        color: '#1f2937',
        height: 1,
        offsetX: 0,
        offsetY: 0
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
          colors: '#6b7280',
          fontSize: '10px',
          fontFamily: 'monospace',
          fontWeight: 400
        },
        formatter: (val: number) => {
          // Format without currency symbol for cleaner look
          return val.toFixed(2);
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
        color: '#1f2937',
        width: 1
      }
    },
    grid: {
      borderColor: '#1f2937',
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
        top: 5,
        right: 5,
        bottom: 5,
        left: 5
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
        const data = w.globals.initialSeries[seriesIndex].data[dataPointIndex];
        const label = w.globals.categoryLabels[dataPointIndex];
        const value = typeof data.y === 'number' ? data.y : data.y;
        
        return `
          <div class="px-2 py-1.5 bg-gray-800 border border-gray-600 rounded text-xs">
            <div class="text-gray-400 mb-1">${label}</div>
            <div class="text-white font-semibold">${formatPrice(value)}</div>
          </div>
        `;
      }
    },
    stroke: {
      width: selectedChartType.value === 'line' ? 3 : 2,
      curve: 'smooth',
      colors: selectedChartType.value === 'line' ? ['#3b82f6'] : ['#10b981'],
      lineCap: 'round'
    },
    fill: {
      type: selectedChartType.value === 'area' ? 'gradient' : 'solid',
      gradient: selectedChartType.value === 'area' ? {
        type: 'vertical',
        shadeIntensity: 1,
        gradientToColors: ['#10b981'],
        inverseColors: false,
        opacityFrom: 0.3,
        opacityTo: 0.05,
        stops: [0, 100]
      } : undefined,
      colors: selectedChartType.value === 'area' ? ['#10b981'] : ['transparent']
    },
    dataLabels: {
      enabled: false
    }
  };
  
  return baseOptions;
});

// Watch for timeframe changes
watch(selectedTimeframe, () => {
  // In a real implementation, you would fetch data for the selected timeframe
  console.log('Timeframe changed to:', selectedTimeframe.value);
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
</style>
