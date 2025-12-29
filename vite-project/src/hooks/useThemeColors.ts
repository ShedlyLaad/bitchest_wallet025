import { BITCHEST } from '../theme/colors';

/**
 * Safely read a CSS variable from :root with a fallback.
 * Returns fallback when running in SSR or if the variable is empty.
 */
const getCSSVar = (varName: string, fallback: string): string => {
  try {
    if (typeof window !== 'undefined' && typeof getComputedStyle === 'function') {
      const value = getComputedStyle(document.documentElement).getPropertyValue(varName).trim();
      if (value) return value;
    }
  } catch {
    // ignore and fallback
  }
  return fallback;
};

/**
 * useThemeColors composable
 * Reads CSS variables (if present) and falls back to BITCHEST tokens.
 * Returns both canonical token names and several legacy aliases for backward compatibility.
 */
export function useThemeColors() {
  const accentGreen = getCSSVar('--accent-green', BITCHEST.accentGreen);
  const accentRed = getCSSVar('--accent-red', BITCHEST.accentRed);
  const bg = getCSSVar('--bg', BITCHEST.bg);
  const blueDark = getCSSVar('--blue-dark', BITCHEST.blueDark);
  const blue = getCSSVar('--blue', BITCHEST.blue);

  return {
    // BitChest tokens (primary)
    accentGreen,
    accentRed,
    bg,
    blueDark,
    blue,

    // Legacy aliases for backward compatibility
    green: accentGreen,
    red: accentRed,
    white: bg,
    lightBlue: blue,
    success: accentGreen,
    danger: accentRed,
    primary: blueDark,
    secondary: blue,

    // Chart colors (BitChest only)
    chart: {
      positive: accentGreen,
      negative: accentRed,
      grid: blueDark,
      text: bg,
      line: blue
    },

    // BitChest reference
    bitchest: BITCHEST
  } as const;
}