<?php

namespace App\Services;

use App\Models\Portfolio;
use App\Models\User;
use App\Models\Transaction;
use App\Models\CryptoCurrency;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Service de gestion des portfolios
 * 
 * Responsabilités :
 * - Mise à jour des portfolios après transactions
 * - Calcul des plus-values selon le cahier des charges
 * - Récupération des détails d'achat
 * - Optimisation via cache Redis
 */
class PortfolioService
{
    private const CACHE_TTL = 300; // 5 minutes
    
    public function __construct(
        private CryptoService $cryptoService
    ) {}

    /**
     * Met à jour le portfolio après une transaction (achat ou vente).
     */
    public function updatePortfolio(
        Portfolio $portfolio,
        Transaction $transaction,
        float $quantity,
        float $price,
        string $type
    ): void {
        if ($type === 'buy') {
            $this->updatePortfolioOnBuy($portfolio, $quantity, $price);
        } elseif ($type === 'sell') {
            $this->updatePortfolioOnSell($portfolio, $transaction, $quantity);
        }
        
        $portfolio->save();
        
        // Invalider le cache du portfolio
        $this->invalidatePortfolioCache($portfolio->id);
    }
    
    /**
     * Met à jour le portfolio lors d'un achat
     */
    private function updatePortfolioOnBuy(Portfolio $portfolio, float $quantity, float $price): void
    {
        $portfolio->total_crypto_value += ($quantity * $price);
    }
    
    /**
     * Met à jour le portfolio lors d'une vente
     */
    private function updatePortfolioOnSell(Portfolio $portfolio, Transaction $transaction, float $quantity): void
    {
        // Calculer la quantité totale détenue AVANT cette transaction
        $totalBuyQuantity = Transaction::getCachedQuantity($portfolio->id, 'buy');
        $totalSellQuantity = Transaction::getCachedQuantity($portfolio->id, 'sell');
        
        // Soustraire la transaction actuelle car elle n'est pas encore dans le cache
        if ($transaction->type === 'buy') {
            $totalBuyQuantity -= (float) $transaction->quantity;
        } elseif ($transaction->type === 'sell') {
            $totalSellQuantity -= (float) $transaction->quantity;
        }
        
        $totalQuantityBefore = max(0, $totalBuyQuantity - $totalSellQuantity);
        
        // Calculer la valeur moyenne investie par unité AVANT la vente
        $averageInvestedValue = $totalQuantityBefore > 0
            ? $portfolio->total_crypto_value / $totalQuantityBefore
            : 0;
        
        // Réduire la valeur investie proportionnellement à la quantité vendue
        $portfolio->total_crypto_value -= ($quantity * $averageInvestedValue);
        
        // S'assurer que la valeur ne devient pas négative
        $portfolio->total_crypto_value = max(0, $portfolio->total_crypto_value);
    }
    
    /**
     * Récupère le portfolio de l'utilisateur avec calculs dynamiques
     * 
     * Logique selon cahier des charges :
     * 1. Coût total = somme de tous les achats
     * 2. Quantité possédée = quantité achetée - quantité vendue
     * 3. Prix moyen d'achat = Coût total / Quantité totale achetée
     * 4. Plus-value = (Quantité possédée × Prix actuel) - (Quantité possédée × Prix moyen d'achat)
     * 
     * @param User $user Utilisateur
     * @return Collection Collection de portfolios enrichis
     */
    public function getUserPortfolio(User $user): Collection
    {
        $portfolios = $user->portfolios()
            ->with('crypto')
            ->get();
        
        return $portfolios->map(function ($portfolio) {
            return $this->enrichPortfolio($portfolio);
        })->filter(function ($portfolio) {
            // Ne retourner que les portfolios avec une quantité > 0
            return ($portfolio->quantity ?? 0) > 0;
        })->values();
    }
    
    /**
     * Enrichit un portfolio avec les calculs dynamiques
     */
    private function enrichPortfolio(Portfolio $portfolio): Portfolio
    {
        // Récupérer les quantités depuis le cache
        $totalBuyQuantity = (float) Transaction::getCachedQuantity($portfolio->id, 'buy');
        $totalSellQuantity = (float) Transaction::getCachedQuantity($portfolio->id, 'sell');
        $quantity = max(0, $totalBuyQuantity - $totalSellQuantity);
        
        // Calculer le coût total des achats (avec cache)
        $totalCost = $this->getTotalCost($portfolio->id);
        
        // Nombre de transactions d'achat (avec cache)
        $buyTransactionsCount = $this->getBuyTransactionsCount($portfolio->id);
        
        // Récupérer le prix courant
        $crypto = CryptoCurrency::find($portfolio->crypto_currency_id);
        $currentPrice = $this->cryptoService->getCachedCurrentPrice($crypto->symbol) ?? 0.0;
        
        // Calculs selon le cahier des charges
        $currentValue = $quantity * $currentPrice;
        $averagePurchasePrice = $totalBuyQuantity > 0 ? ($totalCost / $totalBuyQuantity) : 0;
        $totalInvestedValue = $averagePurchasePrice * $quantity;
        $gainLoss = $currentValue - $totalInvestedValue;
        $gainLossPercent = $totalInvestedValue > 0
            ? ($gainLoss / $totalInvestedValue) * 100
            : null;
        
        // Ajouter les champs calculés au portfolio
        $portfolio->quantity = round($quantity, 8);
        $portfolio->current_price = round($currentPrice, 8);
        $portfolio->current_value = round($currentValue, 8);
        $portfolio->average_purchase_price = round($averagePurchasePrice, 8);
        $portfolio->total_invested_value = round($totalInvestedValue, 8);
        $portfolio->total_cost = round($totalCost, 8);
        $portfolio->gain_loss = round($gainLoss, 8);
        $portfolio->gain_loss_percent = $gainLossPercent !== null ? round($gainLossPercent, 2) : null;
        $portfolio->buy_transactions_count = $buyTransactionsCount;
        $portfolio->total_buy_quantity = $totalBuyQuantity;
        $portfolio->total_sell_quantity = $totalSellQuantity;
        
        return $portfolio;
    }
    
    /**
     * Récupère le coût total des achats (avec cache)
     */
    private function getTotalCost(int $portfolioId): float
    {
        $cacheKey = "portfolio:{$portfolioId}:total_cost";
        
        return (float) Cache::remember($cacheKey, self::CACHE_TTL, function () use ($portfolioId) {
            return Transaction::where('portfolio_id', $portfolioId)
                ->where('type', 'buy')
                ->get()
                ->sum(function ($tx) {
                    return (float) $tx->quantity * (float) $tx->price_at_transaction;
                });
        });
    }
    
    /**
     * Récupère le nombre de transactions d'achat (avec cache)
     */
    private function getBuyTransactionsCount(int $portfolioId): int
    {
        $cacheKey = "portfolio:{$portfolioId}:buy_count";
        
        return (int) Cache::remember($cacheKey, self::CACHE_TTL, function () use ($portfolioId) {
            return Transaction::where('portfolio_id', $portfolioId)
                ->where('type', 'buy')
                ->count();
        });
    }
    
    /**
     * Récupère les détails des transactions d'achat pour une crypto
     * 
     * @param User $user Utilisateur
     * @param int $cryptoCurrencyId ID de la cryptomonnaie
     * @return Collection Collection des détails d'achat
     */
    public function getPurchaseDetails(User $user, int $cryptoCurrencyId): Collection
    {
        $portfolio = Portfolio::where('user_id', $user->id)
            ->where('crypto_currency_id', $cryptoCurrencyId)
            ->first();
        
        if (!$portfolio) {
            return collect([]);
        }
        
        $cacheKey = "portfolio:{$portfolio->id}:purchase_details";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($portfolio) {
            return Transaction::where('portfolio_id', $portfolio->id)
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
    
    /**
     * Invalide le cache d'un portfolio
     */
    private function invalidatePortfolioCache(int $portfolioId): void
    {
        Cache::forget("portfolio:{$portfolioId}:total_cost");
        Cache::forget("portfolio:{$portfolioId}:buy_count");
        Cache::forget("portfolio:{$portfolioId}:purchase_details");
    }
}
