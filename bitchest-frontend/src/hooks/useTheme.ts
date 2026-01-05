import { reactive } from 'vue';
import { theme, BITCHEST } from '../theme/colors';

/**
 * useTheme composable
 * Returns semantic class names and color tokens derived from the BITCHEST palette
 * and an optional runtime `theme.bitchest` override.
 */
export function useTheme() {
  // Use only chart colors (BitChest) as requested
  const chart = {
    positive: (theme.bitchest?.accentGreen) ?? BITCHEST.accentGreen,
    negative: (theme.bitchest?.accentRed) ?? BITCHEST.accentRed,
    grid: (theme.bitchest?.blueDark) ?? BITCHEST.blueDark,
    line: (theme.bitchest?.blue) ?? BITCHEST.blue,
    bg: (theme.bitchest?.bg) ?? BITCHEST.bg
  };

  const colors = {
    // map existing semantic keys to chart tokens
    success: chart.positive,
    danger: chart.negative,
    white: chart.bg,
    primary: chart.grid,
    secondary: chart.line,
    // keep bitchest reference for direct access if needed
    bitchest: theme.bitchest ?? BITCHEST
  };

  const buttonClasses = {
    primary: 'bg-theme-primary hover:bg-theme-secondary text-theme-white',
    success: 'bg-theme-success hover:opacity-90 text-theme-white',
    danger: 'bg-theme-danger hover:opacity-90 text-theme-white',
    secondary: 'bg-theme-secondary hover:opacity-90 text-theme-white'
  };

  const iconClasses = {
    primary: 'text-theme-primary',
    secondary: 'text-theme-secondary',
    success: 'text-theme-success',
    danger: 'text-theme-danger',
    white: 'text-theme-white'
  };

  // cssVars pour usage opt-in (ex : inline style)
  const cssVars = {
    '--accent-green': (theme.bitchest?.accentGreen) ?? BITCHEST.accentGreen,
    '--accent-red': (theme.bitchest?.accentRed) ?? BITCHEST.accentRed,
    '--bg': (theme.bitchest?.bg) ?? BITCHEST.bg,
    '--blue-dark': (theme.bitchest?.blueDark) ?? BITCHEST.blueDark,
    '--blue': (theme.bitchest?.blue) ?? BITCHEST.blue
  };

  // Reactive wrapper so consumers can destructure and still keep reactivity if needed
  return reactive({
    colors,
    buttonClasses,
    iconClasses,
    cssVars
  });
}