import { Cryptocurrency } from '../types';
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
 * Supported cryptocurrencies for the admin market page
 * All prices are in EUR
 * Restricted to exactly 10 cryptos: BTC, ETH, XRP, BCH, ADA, LTC, XEM, XLM, IOTA, DASH
 */
export const adminCryptos: Cryptocurrency[] = [
  {
    id: 'bitcoin',
    symbol: 'BTC',
    name: 'Bitcoin',
    price: 43250.50, // EUR
    change24h: 2.45,
    marketCap: '€847.2B',
    volume24h: '€15.2B',
    icon: bitcoinIcon
  },
  {
    id: 'ethereum',
    symbol: 'ETH',
    name: 'Ethereum',
    price: 2580.75, // EUR
    change24h: -1.32,
    marketCap: '€310.5B',
    volume24h: '€8.7B',
    icon: ethereumIcon
  },
  {
    id: 'ripple',
    symbol: 'XRP',
    name: 'Ripple',
    price: 0.58, // EUR
    change24h: 1.25,
    marketCap: '€28.5B',
    volume24h: '€1.2B',
    icon: rippleIcon
  },
  {
    id: 'bitcoin-cash',
    symbol: 'BCH',
    name: 'Bitcoin Cash',
    price: 225.80, // EUR
    change24h: 0.95,
    marketCap: '€4.3B',
    volume24h: '€1.1B',
    icon: bitcoinCashIcon
  },
  {
    id: 'cardano',
    symbol: 'ADA',
    name: 'Cardano',
    price: 0.35, // EUR
    change24h: -0.85,
    marketCap: '€12.3B',
    volume24h: '€0.5B',
    icon: cardanoIcon
  },
  {
    id: 'litecoin',
    symbol: 'LTC',
    name: 'Litecoin',
    price: 65.42, // EUR
    change24h: 1.15,
    marketCap: '€4.8B',
    volume24h: '€0.9B',
    icon: litecoinIcon
  },
  {
    id: 'nem',
    symbol: 'XEM',
    name: 'NEM',
    price: 0.12, // EUR
    change24h: -2.15,
    marketCap: '€1.1B',
    volume24h: '€0.2B',
    icon: nemIcon
  },
  {
    id: 'stellar',
    symbol: 'XLM',
    name: 'Stellar',
    price: 0.11, // EUR
    change24h: 0.75,
    marketCap: '€2.9B',
    volume24h: '€0.3B',
    icon: stellarIcon
  },
  {
    id: 'iota',
    symbol: 'IOTA',
    name: 'IOTA',
    price: 0.25, // EUR
    change24h: -1.45,
    marketCap: '€0.7B',
    volume24h: '€0.1B',
    icon: iotaIcon
  },
  {
    id: 'dash',
    symbol: 'DASH',
    name: 'Dash',
    price: 45.30, // EUR
    change24h: 0.55,
    marketCap: '€0.5B',
    volume24h: '€0.2B',
    icon: dashIcon
  }
];

// Export for backward compatibility
export const cryptocurrencies = adminCryptos;

