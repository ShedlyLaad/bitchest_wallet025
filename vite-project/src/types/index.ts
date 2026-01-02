export type Role = 'admin' | 'client';
export type UserStatus = 'pending' | 'pending_validation' | 'active' | 'blocked';

export interface AuthUser {
  id: number;
  name: string;
  first_name?: string | null;
  last_name?: string | null;
  phone?: string | null;
  email: string;
  role: Role;
  status: UserStatus;
  must_change_password?: boolean;
  euro_balance?: number;
  profile_picture?: string | null;
  profile_banner?: string | null;
  created_at?: string;
  updated_at?: string;
}

export interface LoginPayload {
  email: string;
  password: string;
}

export interface RegisterPayload {
  first_name: string;
  last_name: string;
  email: string;
  email_confirmation: string;
}

export interface AuthResponse {
  user: AuthUser;
  token: string;
  must_change_password?: boolean;
  message?: string;
}

export interface CryptoCurrency {
  id: number;
  name: string;
  symbol: string;
  price?: number;
  change24h?: number;
  marketCap?: number;
  volume24h?: number;
}

export interface CryptoPricePoint {
  crypto_currency_id: number;
  price: number;
  recorded_at: string;
}

export interface PortfolioPosition {
  id: number;
  user_id: number;
  crypto_currency_id: number;
  total_crypto_value: number;
  quantity?: number;
  current_price?: number;
  current_value?: number;
  invested_value?: number;
  average_purchase_price?: number;
  total_invested_value?: number;
  total_cost?: number;
  gain_loss?: number;
  gain_loss_percent?: number | null;
  buy_transactions_count?: number;
  total_buy_quantity?: number;
  total_sell_quantity?: number;
  crypto?: CryptoCurrency;
}

export interface PortfolioResponse {
  balance: number;
  portfolio: PortfolioPosition[];
}

export interface Transaction {
  id: number;
  portfolio_id: number;
  type: 'buy' | 'sell';
  quantity: number;
  price_at_transaction: number;
  euro_amount: number;
  created_at: string;
  updated_at: string;
  portfolio?: {
    crypto?: CryptoCurrency;
    user?: AuthUser;
  };
}

export interface Paginated<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface BuyPayload {
  symbol: string;
  quantity: number;
}

export interface SellPayload {
  symbol: string;
  quantity: number;
}

export interface AdminCreateUserPayload {
  name: string;
  email: string;
}

export interface AdminCreateUserResponse {
  user: AuthUser;
  temporary_password: string;
}

export interface Notification {
  id: number;
  user_id: number;
  portfolio_id?: number;
  crypto_currency_id?: number;
  type: 'profit' | 'loss' | 'price_alert' | 'portfolio_update';
  title: string;
  message: string;
  crypto_symbol?: string;
  gain_loss?: number;
  gain_loss_percent?: number;
  current_price?: number;
  previous_price?: number;
  is_read: boolean;
  read_at?: string;
  created_at: string;
  updated_at: string;
  crypto?: CryptoCurrency;
  portfolio?: PortfolioPosition;
}