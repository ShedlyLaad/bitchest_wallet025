// Theme tokens and legacy theme exports
export const BITCHEST = {
    accentGreen: '#01FF19',
    accentRed: '#FF5964',
    bg: '#FFFFFF',
    blueDark: '#38618C',
    blue: '#35A7FF',
  } as const;
  
  // Legacy theme structure (deprecated - prefer BITCHEST or CSS variables)
  const _baseTheme = {
    colors: {
      green: BITCHEST.accentGreen,    // @deprecated Use BITCHEST.accentGreen
      red: BITCHEST.accentRed,        // @deprecated Use BITCHEST.accentRed
      white: BITCHEST.bg,             // @deprecated Use BITCHEST.bg
      blue: BITCHEST.blueDark,        // @deprecated Use BITCHEST.blueDark
      lightBlue: BITCHEST.blue        // @deprecated Use BITCHEST.blue
    },
    gradients: {
      // @deprecated Use CSS variables in gradients
      primary: `linear-gradient(to right, ${BITCHEST.blueDark}, ${BITCHEST.blue})`,
      secondary: `linear-gradient(to right, ${BITCHEST.accentGreen}, ${BITCHEST.blue})`,
      danger: `linear-gradient(to right, ${BITCHEST.accentRed}, ${BITCHEST.blueDark})`
    }
  };
  
  // Exported theme object kept for backward compatibility
  export const theme = {
    ..._baseTheme,
    bitchest: BITCHEST
  };