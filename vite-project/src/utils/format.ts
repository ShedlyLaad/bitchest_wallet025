/**
 * Formats a number as EUR currency
 * @param value - The number to format
 * @returns Formatted string in EUR format (e.g., "€ 1,234.56")
 */
export const formatEUR = (value: number | null | undefined): string => {
  if (value === null || value === undefined || isNaN(value)) {
    return '€ 0.00';
  }
  
  return new Intl.NumberFormat('en-GB', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value);
};

