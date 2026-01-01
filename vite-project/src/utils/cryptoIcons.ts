import bitcoinIcon from '../assets/bitcoin.png';
import ethereumIcon from '../assets/ethereum.png';
import rippleIcon from '../assets/ripple.png';
import bitcoinCashIcon from '../assets/bitcoin-cash.png';
import cardanoIcon from '../assets/cardano.png';
import litecoinIcon from '../assets/litecoin.png';
import nemIcon from '../assets/nem.png';
import stellarIcon from '../assets/stellar.png';
import iotaIcon from '../assets/iota.png';
import dashIcon from '../assets/dash.png';

/**
 * Map of cryptocurrency symbols to their icon images
 * Supports both standard symbols (BTC, ETH, etc.) and alternative symbols (MIOTA -> IOTA)
 */
const cryptoIconMap: Record<string, string> = {
  BTC: bitcoinIcon,
  ETH: ethereumIcon,
  XRP: rippleIcon,
  BCH: bitcoinCashIcon,
  ADA: cardanoIcon,
  LTC: litecoinIcon,
  XEM: nemIcon,
  XLM: stellarIcon,
  IOTA: iotaIcon,
  MIOTA: iotaIcon, // IOTA is also known as MIOTA
  DASH: dashIcon,
};

/**
 * Get the icon image for a cryptocurrency symbol
 * @param symbol - The cryptocurrency symbol (e.g., 'BTC', 'ETH', 'IOTA', 'MIOTA')
 * @returns The icon image path, or undefined if not found
 */
export function getCryptoIcon(symbol: string): string | undefined {
  if (!symbol) return undefined;
  return cryptoIconMap[symbol.toUpperCase()];
}

/**
 * Get the icon image for a cryptocurrency symbol with a fallback
 * @param symbol - The cryptocurrency symbol
 * @param fallback - Optional fallback icon if not found
 * @returns The icon image path, or the fallback if not found
 */
export function getCryptoIconWithFallback(symbol: string, fallback?: string): string {
  return getCryptoIcon(symbol) || fallback || '';
}

/**
 * Check if an icon exists for a given symbol
 * @param symbol - The cryptocurrency symbol
 * @returns true if icon exists, false otherwise
 */
export function hasCryptoIcon(symbol: string): boolean {
  return !!getCryptoIcon(symbol);
}

export default cryptoIconMap;

