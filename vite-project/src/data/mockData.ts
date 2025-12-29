// src/data/mockData.ts
import { Cryptocurrency, ChartData, Partner } from '../types';
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

export const adminStats = {
  totalUsers: 1248,
  activeUsers: 832,
  revenue: 245000,
  pendingKyc: 16
};

export const currentUser = {
  id: 'user123',
  name: 'John Doe',
  email: 'john.doe@example.com',
  createdAt: '2023-09-18T10:15:00Z',
  isVerified: true,
  balance: 43250.75,
  role: 'client' as 'client' | 'admin'
};

export const adminUser = {
  id: 'admin001',
  name: 'Admin',
  email: 'admin@eqanaouita.com',
  createdAt: '2023-01-01T00:00:00Z',
  isVerified: true,
  role: 'admin' as 'client' | 'admin',
  avatar: null
};

export const transactions = [
  {
    id: 'tx1',
    type: 'buy',
    cryptocurrency: 'Bitcoin',
    amount: 0.5,
    price: 20000,
    total: 10000,
    timestamp: '2024-06-01T08:00:00Z',
    status: 'completed'
  },
  {
    id: 'tx2',
    type: 'sell',
    cryptocurrency: 'Ethereum',
    amount: 2,
    price: 1700,
    total: 3400,
    timestamp: '2024-06-03T10:30:00Z',
    status: 'pending'
  },
  {
    id: 'tx3',
    type: 'buy',
    cryptocurrency: 'Cardano',
    amount: 1000,
    price: 0.35,
    total: 350,
    timestamp: '2024-06-05T14:45:00Z',
    status: 'cancelled'
  }
];
export const cryptocurrencies: Cryptocurrency[] = [
  {
    id: 'bitcoin',
    symbol: 'BTC',
    name: 'Bitcoin',
    price: 43250.50,
    change24h: 2.45,
    marketCap: '$847.2B',
    volume24h: '$15.2B',
    icon: bitcoinIcon
  },
  {
    id: 'ethereum',
    symbol: 'ETH',
    name: 'Ethereum',
    price: 2580.75,
    change24h: -1.32,
    marketCap: '$310.5B',
    volume24h: '$8.7B',
    icon: ethereumIcon
  },
  {
    id: 'ripple',
    symbol: 'XRP',
    name: 'Ripple',
    price: 0.58,
    change24h: 1.25,
    marketCap: '$28.5B',
    volume24h: '$1.2B',
    icon: rippleIcon
  },
  {
    id: 'bitcoin-cash',
    symbol: 'BCH',
    name: 'Bitcoin Cash',
    price: 225.80,
    change24h: 0.95,
    marketCap: '$4.3B',
    volume24h: '$1.1B',
    icon: bitcoinCashIcon
  },
  {
    id: 'cardano',
    symbol: 'ADA',
    name: 'Cardano',
    price: 0.35,
    change24h: -0.85,
    marketCap: '$12.3B',
    volume24h: '$0.5B',
    icon: cardanoIcon
  },
  {
    id: 'litecoin',
    symbol: 'LTC',
    name: 'Litecoin',
    price: 65.42,
    change24h: 1.15,
    marketCap: '$4.8B',
    volume24h: '$0.9B',
    icon: litecoinIcon
  },
  {
    id: 'nem',
    symbol: 'XEM',
    name: 'NEM',
    price: 0.12,
    change24h: -2.15,
    marketCap: '$1.1B',
    volume24h: '$0.2B',
    icon: nemIcon
  },
  {
    id: 'stellar',
    symbol: 'XLM',
    name: 'Stellar',
    price: 0.11,
    change24h: 0.75,
    marketCap: '$2.9B',
    volume24h: '$0.3B',
    icon: stellarIcon
  },
  {
    id: 'iota',
    symbol: 'MIOTA',
    name: 'IOTA',
    price: 0.25,
    change24h: -1.45,
    marketCap: '$0.7B',
    volume24h: '$0.1B',
    icon: iotaIcon
  },
  {
    id: 'dash',
    symbol: 'DASH',
    name: 'Dash',
    price: 45.30,
    change24h: 0.55,
    marketCap: '$0.5B',
    volume24h: '$0.2B',
    icon: dashIcon
  }
];

export const chartData: ChartData[] = [
  { time: '00:00', price: 43000 },
  { time: '02:00', price: 43120 },
  { time: '04:00', price: 43150 },
  { time: '06:00', price: 43080 },
  { time: '08:00', price: 42900 },
  { time: '10:00', price: 43010 },
  { time: '12:00', price: 43300 },
  { time: '14:00', price: 43420 },
  { time: '16:00', price: 43250 },
  { time: '18:00', price: 43380 },
  { time: '20:00', price: 43400 },
  { time: '22:00', price: 43320 },
  { time: '24:00', price: 43250 }
];

// Portfolio data structure
export interface Purchase {
  date: string;
  quantity: number;
  price: number;
}

export interface PortfolioItem {
  cryptoId: string;
  purchases: Purchase[];
}

export const portfolioData: PortfolioItem[] = [
  {
    cryptoId: 'bitcoin',
    purchases: [
      { date: '2024-01-15T10:00:00Z', quantity: 0.25, price: 42000 },
      { date: '2024-02-20T14:30:00Z', quantity: 0.15, price: 43000 },
      { date: '2024-03-10T09:15:00Z', quantity: 0.1, price: 41000 }
    ]
  },
  {
    cryptoId: 'ethereum',
    purchases: [
      { date: '2024-01-20T11:00:00Z', quantity: 2.5, price: 2500 },
      { date: '2024-02-25T15:45:00Z', quantity: 1.5, price: 2600 }
    ]
  },
  {
    cryptoId: 'cardano',
    purchases: [
      { date: '2024-02-01T08:00:00Z', quantity: 5000, price: 0.32 },
      { date: '2024-03-05T12:00:00Z', quantity: 3000, price: 0.34 },
      { date: '2024-04-10T16:30:00Z', quantity: 2000, price: 0.33 }
    ]
  },
  {
    cryptoId: 'ripple',
    purchases: [
      { date: '2024-01-25T13:20:00Z', quantity: 10000, price: 0.55 }
    ]
  }
];

export const partners: Partner[] = [
  {
    id: 'bitcoin',
    name: "Bitcoin Network",
    icon: bitcoinIcon,
    value: "$890B",
    growth: "+12.5%",
    description: "Premier réseau de cryptomonnaie décentralisé"
  },
  {
    id: 'ethereum',
    name: "Ethereum Network",
    icon: ethereumIcon,
    value: "$320B",
    growth: "+8.3%",
    description: "Réseau décentralisé pour les applications blockchain"
  },
  {
    id: 'ripple',
    name: "RippleNet",
    icon: rippleIcon,
    value: "$29B",
    growth: "+10.1%",
    description: "Réseau de paiement numérique pour les institutions financières"
  },
  {
    id: 'bitcoin-cash',
    name: "Bitcoin Cash Network",
    icon: bitcoinCashIcon,
    value: "$4.5B",
    growth: "+6.7%",
    description: "Version améliorée de Bitcoin pour des transactions plus rapides"
  },
  {
    id: 'cardano',
    name: "Cardano Network",
    icon: cardanoIcon,
    value: "$12.5B",
    growth: "+5.4%",
    description: "Plateforme de contrat intelligent avec une approche basée sur la recherche"
  },
  {
    id: 'litecoin',
    name: "Litecoin Network",
    icon: litecoinIcon,
    value: "$4.9B",
    growth: "+7.2%",
    description: "Cryptomonnaie peer-to-peer qui permet des paiements instantanés"
  },
  {
    id: 'nem',
    name: "NEM Network",
    icon: nemIcon,
    value: "$1.2B",
    growth: "+3.8%",
    description: "Plateforme de blockchain pour les entreprises avec une fonctionnalité de chaîne latérale"
  },
  {
    id: 'stellar',
    name: "Stellar Network",
    icon: stellarIcon,
    value: "$3.1B",
    growth: "+9.0%",
    description: "Réseau décentralisé qui relie les banques, les paiements et les personnes"
  },
  {
    id: 'iota',
    name: "IOTA Network",
    icon: iotaIcon,
    value: "$0.8B",
    growth: "+2.5%",
    description: "Plateforme de blockchain pour l'Internet des objets (IoT)"
  },
  {
    id: 'dash',
    name: "Dash Network",
    icon: dashIcon,
    value: "$0.6B",
    growth: "+4.1%",
    description: "Cryptomonnaie axée sur la confidentialité et la rapidité des transactions"
  }
];
