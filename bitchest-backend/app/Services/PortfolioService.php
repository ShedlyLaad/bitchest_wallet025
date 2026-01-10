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

    /**
     * Met à jour le portfolio après une transaction
     * 
     * Note: total_crypto_value est utilisé pour le suivi interne, mais les calculs
     * de plus-value sont faits dynamiquement dans getUserPortfolio() à partir des transactions
     * selon le cahier des charges.
     */
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
            // Utiliser le cache puis soustraire la transaction actuelle
            $totalBuyQuantity = \App\Models\Transaction::getCachedQuantity($portfolio->id, 'buy');
            $totalSellQuantity = \App\Models\Transaction::getCachedQuantity($portfolio->id, 'sell');
            
            // Soustraire la transaction actuelle car elle n'est pas encore dans le cache
            if ($transaction->type === 'buy') {
                $totalBuyQuantity -= (float) $transaction->quantity;
            } elseif ($transaction->type === 'sell') {
                $totalSellQuantity -= (float) $transaction->quantity;
            }
            
            $totalQuantityBefore = $totalBuyQuantity - $totalSellQuantity;
            
            // Calculer la valeur moyenne investie par unité AVANT la vente
            $averageInvestedValue = $totalQuantityBefore > 0 
                ? $portfolio->total_crypto_value / $totalQuantityBefore 
                : 0;
            
            // Réduire la valeur investie proportionnellement à la quantité vendue
            // Cela permet de maintenir la cohérence du total_crypto_value
            $portfolio->total_crypto_value -= ($quantity * $averageInvestedValue);
            
            // Si la valeur crypto devient négative ou nulle, la mettre à zéro
            if ($portfolio->total_crypto_value <= 0) {
                $portfolio->total_crypto_value = 0;
            }
        }

        $portfolio->save();
        
        // Invalider le cache du portfolio après mise à jour
        \Illuminate\Support\Facades\Cache::forget("portfolio:{$portfolio->id}:total_cost");
        \Illuminate\Support\Facades\Cache::forget("portfolio:{$portfolio->id}:buy_count");
        \Illuminate\Support\Facades\Cache::forget("portfolio:{$portfolio->id}:purchase_details");
    }

    /**
     * Récupère le portfolio de l'utilisateur avec calculs dynamiques selon le cahier des charges
     * 
     * Logique selon cahier des charges :
     * 1. Coût total = somme de tous les achats (même ceux partiellement vendus)
     * 2. Quantité possédée = quantité achetée - quantité vendue
     * 3. Valeur d'achat d'une unité = Coût total / Quantité possédée
     * 4. Plus-value = (Quantité possédée × Prix actuel) - (Quantité possédée × Valeur d'achat d'une unité)
     */
    public function getUserPortfolio(User $user)
    {
        $portfolios = $user->portfolios()
            ->with('crypto')
            ->get();

        // Enrichir chaque portfolio avec quantité détenue, prix courant, valeur courante, gain/perte
        // Utiliser Redis cache pour des performances optimales
        return $portfolios->map(function ($portfolio) {
            // Utiliser le cache Redis pour les quantités (beaucoup plus rapide)
            $totalBuyQuantity = (float) \App\Models\Transaction::getCachedQuantity($portfolio->id, 'buy');
            $totalSellQuantity = (float) \App\Models\Transaction::getCachedQuantity($portfolio->id, 'sell');
            
            // Pour le calcul du coût total, on a besoin des détails des transactions d'achat
            // On utilise un cache séparé pour cela
            $cacheKey = "portfolio:{$portfolio->id}:total_cost";
            $totalCost = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($portfolio) {
                $buyTransactions = \App\Models\Transaction::where('portfolio_id', $portfolio->id)
                    ->where('type', 'buy')
                    ->get();
                
                return (float) $buyTransactions->sum(function ($tx) {
                    return (float) $tx->quantity * (float) $tx->price_at_transaction;
                });
            });
            
            // Pour le nombre de transactions d'achat
            $cacheKeyCount = "portfolio:{$portfolio->id}:buy_count";
            $buyTransactionsCount = \Illuminate\Support\Facades\Cache::remember($cacheKeyCount, 300, function () use ($portfolio) {
                return \App\Models\Transaction::where('portfolio_id', $portfolio->id)
                    ->where('type', 'buy')
                    ->count();
            });
            
            // Quantité actuellement possédée (selon cahier des charges)
            // Exemple : 1 + 0.5 + 0.5 = 2 BTC
            $quantity = $totalBuyQuantity - $totalSellQuantity;
            
            // Récupérer le prix courant en temps réel via CryptoService (avec Coinbase)
            $crypto = \App\Models\CryptoCurrency::find($portfolio->crypto_currency_id);
            $currentPrice = $this->cryptoService->getCurrentPrice($crypto->symbol) ?? 0.0;
            
            // Valeur totale au cours actuel (selon cahier des charges)
            // Exemple : (1 + 0.5 + 0.5) × 30000 = 60000 euros
            $currentValue = $quantity * $currentPrice;
            
            // Valeur d'achat d'une unité (selon cahier des charges)
            // Prix moyen d'achat = Coût total de tous les achats / Quantité totale achetée
            // Exemple : 29000 / 2 = 14500 euros par BTC
            $averagePurchasePrice = $totalBuyQuantity > 0 ? ($totalCost / $totalBuyQuantity) : 0;
            
            // Coût total investi pour la quantité possédée
            // C'est le prix moyen d'achat multiplié par la quantité possédée
            // Exemple : 14500 × 1 = 14500 euros (si on a vendu 1 BTC sur 2)
            $totalInvestedValue = $averagePurchasePrice * $quantity;
            
            // Plus-value actuelle (selon cahier des charges)
            // Exemple : 60000 - 29000 = 31000 euros
            // Note: Dans l'exemple du cahier, il y a une incohérence (60000 - 14500 = 45500)
            // mais la logique correcte est : Valeur actuelle - Coût total investi
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
            
            // Informations sur les transactions (utiliser les valeurs mises en cache)
            $portfolio->buy_transactions_count = $buyTransactionsCount;
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
        // Utiliser le cache Redis pour des performances optimales
        $cacheKey = "portfolio:{$portfolio->id}:purchase_details";
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($portfolio) {
            return \App\Models\Transaction::where('portfolio_id', $portfolio->id)
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
        });
    }
}
