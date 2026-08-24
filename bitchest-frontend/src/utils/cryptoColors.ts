// Single source of truth for per-cryptocurrency brand colors, so every chart
// and card across the app (landing page, admin dashboard, trading charts)
// keys the same coin to the same color instead of each component picking
// its own palette.
const CRYPTO_COLORS: Record<string, string> = {
  BTC: '#F7931A',
  ETH: '#627EEA',
  XRP: '#0085c3',
  BCH: '#0ac18e',
  ADA: '#0033ad',
  LTC: '#345d9d',
  XEM: '#67B2E8',
  XLM: '#7d00ff',
  MIOTA: '#00d4ff',
  DASH: '#008de4',
};

const DEFAULT_CRYPTO_COLOR = '#35a7ff'; // brand blue fallback for unlisted symbols

export function getCryptoColor(symbol: string): string {
  return CRYPTO_COLORS[symbol.toUpperCase()] || DEFAULT_CRYPTO_COLOR;
}
