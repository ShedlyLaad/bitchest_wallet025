<template>
  <div class="w-full rounded-xl overflow-hidden bg-primary/10 p-2 sm:p-4">
    <ApexChart
      v-if="apexSeries"
      :options="options"
      :series="apexSeries"
      :type="type"
      :height="chartHeightProp"
      width="100%"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, watch } from 'vue';
import ApexChart from 'vue3-apexcharts';
import type { ApexOptions } from 'apexcharts';

interface ChartData {
  time: string;
  price: number;
}

interface Props {
  series?: number[];
  symbol?: string;
  mode?: 'positive' | 'negative' | 'neutral';
  data?: ChartData[];
  type?: 'line' | 'area' | 'candlestick';
  height?: number | string;
  showGrid?: boolean;
  showTooltip?: boolean;
  animated?: boolean;
  compact?: boolean;
  currency?: 'USD' | 'EUR';
}

const props = defineProps<Props>();

const {
  series: seriesProp,
  symbol = 'Price',
  mode = 'neutral',
  data: dataProp,
  type = 'area',
  height = 350,
  showGrid = true,
  showTooltip = true,
  animated = true,
  compact = false,
  currency = 'USD',
} = props;

// Helpers
const normalize30d = (series: number[]): number[] => {
  const clamped = series.map(v => Math.max(0, v));
  if (clamped.length === 30) return clamped;
  if (clamped.length > 30) return clamped.slice(-30);
  const last = clamped[clamped.length - 1] || 0;
  return [...clamped, ...Array(30 - clamped.length).fill(last)];
};

const generate30dLabels = (): string[] => {
  const labels: string[] = [];
  const now = new Date();
  for (let i = 29; i >= 0; i--) {
    const date = new Date(now);
    date.setDate(date.getDate() - i);
    labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
  }
  return labels;
};

const accentGreen = 'var(--accent-green)';
const accentRed = 'var(--accent-red)';
const bg = 'var(--bg)';
const blueDark = 'var(--blue-dark)';
const blue = 'var(--blue)';

const currencySymbol = currency === 'EUR' ? '€' : '$';
const formatCurrency = (v: number) => {
  if (currency === 'EUR') {
    return new Intl.NumberFormat('en-GB', {
      style: 'currency',
      currency: 'EUR',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(v);
  }
  return `${currencySymbol}${v.toLocaleString()}`;
};

const strokeColor = mode === 'positive' ? accentGreen : mode === 'negative' ? accentRed : blueDark;
const fillColor = mode === 'positive' ? accentGreen : mode === 'negative' ? accentRed : blue;

const chartData = computed(() => {
  if (seriesProp && seriesProp.length) {
    return {
      chartSeries: normalize30d(seriesProp),
      labels: generate30dLabels()
    };
  } else if (dataProp && dataProp.length) {
    const normalized = normalize30d(dataProp.map(d => d.price));
    return {
      chartSeries: normalized,
      labels: dataProp.map(d => d.time).slice(-30)
    };
  } else {
    return {
      chartSeries: Array(30).fill(0),
      labels: generate30dLabels()
    };
  }
});

const apexSeries = computed(() => [{
  name: symbol || 'Price',
  data: chartData.value.chartSeries.map((value, index) => ({ x: chartData.value.labels[index] || '', y: value }))
}]);

const chartHeightProp = computed(() => (typeof height === 'string' ? height : height || 350));

const options = computed<ApexOptions>(() => ({
  chart: {
    type,
    height: typeof height === 'number' ? height : undefined,
    background: 'transparent',
    toolbar: {
      show: !compact,
      tools: {
        download: true,
        selection: true,
        zoom: true,
        zoomin: true,
        zoomout: true,
        pan: true,
      },
      export: {
        svg: { filename: 'crypto-chart-vector' },
        png: { filename: 'crypto-chart' },
      },
    },
    zoom: { enabled: !compact, type: 'x' },
    animations: {
      enabled: animated,
      speed: 800,
      animateGradually: { enabled: true, delay: 150 },
      dynamicAnimation: { enabled: true, speed: 350 },
    },
  },
  responsive: [{
    breakpoint: 480,
    options: {
      chart: { height: compact ? 200 : 280 },
      legend: { show: false },
    }
  }],
  stroke: { curve: 'smooth', width: 3, colors: [strokeColor] },
  grid: {
    borderColor: blueDark,
    show: showGrid,
    strokeDashArray: 3,
    position: 'back',
    xaxis: { lines: { show: true } },
    yaxis: { lines: { show: true } },
    padding: { top: 10, right: 10, bottom: 10, left: 10 },
  },
  xaxis: {
    type: 'category',
    categories: chartData.value.labels,
    labels: {
      style: { colors: bg, fontSize: compact ? '10px' : '12px', fontWeight: 500 },
      rotate: compact ? 0 : -45,
      rotateAlways: false,
      hideOverlappingLabels: true,
    },
    axisBorder: { color: blueDark },
    axisTicks: { show: true, color: blue },
    crosshairs: {
      show: !compact,
      width: 1,
      position: 'back',
      opacity: 0.9,
      stroke: { color: blue, width: 1, dashArray: 3 }
    },
  },
  yaxis: {
    labels: { style: { colors: bg, fontSize: compact ? '10px' : '12px', fontWeight: 500 }, formatter: (v: number) => formatCurrency(v) },
    tickAmount: compact ? 4 : 6,
    forceNiceScale: true,
    floating: false,
    decimalsInFloat: 2,
    crosshairs: {
      show: !compact,
      position: 'back',
      stroke: { color: blue, width: 1, dashArray: 3 }
    }
  },
  tooltip: {
    enabled: showTooltip,
    theme: 'dark',
    style: { fontSize: '14px' },
    x: { show: true, format: 'dd MMM yyyy' },
    y: { formatter: (v: number) => formatCurrency(v) },
    marker: { show: true },
    fixed: { enabled: false, position: 'topRight', offsetX: 0, offsetY: 0 },
    custom: ({ series, seriesIndex, dataPointIndex, w }: any) => {
      const value = series[seriesIndex]?.[dataPointIndex] || 0;
      const time = w.globals.labels[dataPointIndex] || '';
      const formattedValue = formatCurrency(value);
      return `
        <div class="px-4 py-3" style="background: ${blueDark}; border: 1px solid ${blue}; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.25);">
          <div style="color: ${blue}; font-size: 12px; margin-bottom: 4px;">${time}</div>
          <div style="color: ${bg}; font-size: 16px; font-weight: 600;">${formattedValue}</div>
        </div>
      `;
    }
  },
  markers: {
    size: compact ? 0 : 0,
    colors: [fillColor],
    strokeColors: bg,
    strokeWidth: 2,
    strokeOpacity: 0.9,
    fillOpacity: 1,
    shape: 'circle',
    hover: { size: 6, sizeOffset: 3 }
  },
  fill: {
    type: 'gradient',
    gradient: {
      type: 'vertical',
      shadeIntensity: 1,
      gradientToColors: [fillColor],
      inverseColors: false,
      opacityFrom: 0.7,
      opacityTo: 0.1,
      stops: [0, 100]
    }
  },
  dataLabels: { enabled: false },
  theme: { mode: 'dark' }
}));

// expose ApexChart component locally
defineExpose({ ApexChart });
</script>

<style scoped>
/* adapt styles to your theme */
</style>