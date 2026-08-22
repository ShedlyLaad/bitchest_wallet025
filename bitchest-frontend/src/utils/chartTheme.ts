// Shared ApexCharts color tokens so every chart in the app (Admin dashboard,
// trading chart, market history) reads as one visual system instead of each
// component picking its own ad-hoc hex palette.
export const CHART_COLORS = {
  blue: 'var(--blue)',
  blueDark: 'var(--blue-dark)',
  green: 'var(--accent-green)',
  red: 'var(--accent-red)',
  axisLabel: '#9ca3af', // gray-400, matches the app's default muted text color
  gridLine: '#374151', // gray-700, matches the app's default border color
} as const;

export const CHART_GRID = {
  borderColor: CHART_COLORS.gridLine,
  strokeDashArray: 3,
  xaxis: { lines: { show: true } },
  yaxis: { lines: { show: true } },
} as const;

export const CHART_AXIS_LABEL_STYLE = {
  colors: CHART_COLORS.axisLabel,
  fontSize: '12px',
} as const;
