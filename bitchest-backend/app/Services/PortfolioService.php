<?php

namespace App\Services;

use App\Models\Portfolio;
use App\Models\User;
use App\Models\CryptoCurrency;

class PortfolioService
{
    private CryptoService $cryptoService;

    public function __construct(CryptoService $cryptoService)
    {
        $this->cryptoService = $cryptoService;
    }

    public function updatePortfolio(Portfolio $portfolio, \App\Models\Transaction $transaction, float $quantity, float $price, string $type)
    {
        // --- BUY ---
        if ($type === 'buy') {
            // Ajouter la valeur investie au total_crypto_value
            // total_crypto_value représente la valeur totale investie en euros pour cette crypto
            $portfolio->total_crypto_value += ($quantity * $price);
        }
        // --- SELL ---
        elseif ($type === 'sell') {
            // Calculer la quantité totale détenue AVANT cette transaction
            // On exclut la transaction actuelle en utilisant son ID
            $totalBuyQuantity = \App\Models\Transaction::where('portfolio_id', $portfolio->id)
                ->where('type', 'buy')
                ->where('id', '!=', $transaction->id)
                ->sum('quantity');
            $totalSellQuantity = \App\Models\Transaction::where('portfolio_id', $portfolio->id)
                ->where('type', 'sell')
                ->where('id', '!=', $transaction->id)
                ->sum('quantity');
            $totalQuantityBefore = $totalBuyQuantity - $totalSellQuantity;
            
            // Calculer la valeur moyenne investie par unité
            $averageInvestedValue = $totalQuantityBefore > 0 
                ? $portfolio->total_crypto_value / $totalQuantityBefore 
                : 0;
            
            // Réduire la valeur investie proportionnellement à la quantité vendue
            $portfolio->total_crypto_value -= ($quantity * $averageInvestedValue);
            
            // Si la valeur crypto devient négative ou nulle, la mettre à zéro
            if ($portfolio->total_crypto_value <= 0) {
                $portfolio->total_crypto_value = 0;
            }
        }

        $portfolio->save();
    }

    public function getUserPortfolio(User $user)
    {
        $portfolios = $user->portfolios()
            ->with('crypto')
            ->get();

        // Enrichir chaque portfolio avec quantité détenue, prix courant, valeur courante, gain/perte
        return $portfolios->map(function ($portfolio) {
            // Récupérer toutes les transactions d'achat
            $buyTransactions = \App\Models\Transaction::where('portfolio_id', $portfolio->id)
                ->where('type', 'buy')
                ->orderBy('created_at')
                ->get();
            
            // Calculer le coût total des achats (selon cahier des charges)
            $totalCost = $buyTransactions->sum(function ($tx) {
                return (float) $tx->quantity * (float) $tx->price_at_transaction;
            });
            
            // Calculer la quantité totale achetée
            $totalBuyQuantity = (float) $buyTransactions->sum('quantity');
            
            // Calculer la quantité totale vendue
            $totalSellQuantity = (float) \App\Models\Transaction::where('portfolio_id', $portfolio->id)
                ->where('type', 'sell')
                ->sum('quantity');
            
            // Quantité actuellement possédée
            $quantity = $totalBuyQuantity - $totalSellQuantity;
            
            // Récupérer le prix courant via CryptoService (avec CoinGecko)
            $crypto = \App\Models\CryptoCurrency::find($portfolio->crypto_currency_id);
            $currentPrice = $this->cryptoService->getCurrentPrice($crypto->symbol) ?? 0.0;
            
            // Valeur totale au cours actuel (selon cahier des charges)
            $currentValue = $quantity * $currentPrice;
            
            // Valeur d'achat moyenne d'une unité (selon cahier des charges)
            // Coût total / Quantité possédée
            $averagePurchasePrice = $quantity > 0 ? ($totalCost / $quantity) : 0;
            
            // Coût total actuel (valeur investie pour la quantité possédée)
            // C'est la valeur d'achat de toute la quantité possédée
            $totalInvestedValue = $averagePurchasePrice * $quantity;
            
            // Plus-value actuelle (selon cahier des charges)
            // Valeur totale au cours actuel - Coût total investi
            $gainLoss = $currentValue - $totalInvestedValue;
            
            // Pourcentage de gain/perte
            $gainLossPercent = $totalInvestedValue > 0 
                ? ($gainLoss / $totalInvestedValue) * 100 
                : null;

            // Append computed fields
            $portfolio->quantity = round($quantity, 8);
            $portfolio->current_price = round($currentPrice, 8);
            $portfolio->current_value = round($currentValue, 8);
            $portfolio->average_purchase_price = round($averagePurchasePrice, 8);
            $portfolio->total_invested_value = round($totalInvestedValue, 8);
            $portfolio->total_cost = round($totalCost, 8);
            $portfolio->gain_loss = round($gainLoss, 8);
            $portfolio->gain_loss_percent = $gainLossPercent !== null ? round($gainLossPercent, 2) : null;
            
            // Informations sur les transactions
            $portfolio->buy_transactions_count = $buyTransactions->count();
            $portfolio->total_buy_quantity = $totalBuyQuantity;
            $portfolio->total_sell_quantity = $totalSellQuantity;

            return $portfolio;
        })->filter(function ($portfolio) {
            // Ne retourner que les portfolios avec une quantité > 0
            return $portfolio->quantity > 0;
        })->values();
    }
    
    /**
     * Récupère les détails des transactions d'achat pour une crypto
     * Retourne la liste des achats avec date, quantité et cours
     */
    public function getPurchaseDetails(User $user, int $cryptoCurrencyId)
    {
        $portfolio = \App\Models\Portfolio::where('user_id', $user->id)
            ->where('crypto_currency_id', $cryptoCurrencyId)
            ->first();
        
        if (!$portfolio) {
            return collect([]);
        }
        
        // Récupérer toutes les transactions d'achat avec leurs détails
        $buyTransactions = \App\Models\Transaction::where('portfolio_id', $portfolio->id)
            ->where('type', 'buy')
            ->orderBy('created_at')
            ->get()
            ->map(function ($tx) {
                return [
                    'id' => $tx->id,
                    'date' => $tx->created_at->format('Y-m-d'),
                    'datetime' => $tx->created_at->toIso8601String(),
                    'quantity' => (float) $tx->quantity,
                    'price' => (float) $tx->price_at_transaction,
                    'total_cost' => (float) $tx->quantity * (float) $tx->price_at_transaction,
                ];
            });
        
        return $buyTransactions;
    }
}
