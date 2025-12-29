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
            $totalBuyQuantity = \App\Models\Transaction::where('portfolio_id', $portfolio->id)
                ->where('type', 'buy')
                ->sum('quantity');
            $totalSellQuantity = \App\Models\Transaction::where('portfolio_id', $portfolio->id)
                ->where('type', 'sell')
                ->sum('quantity');

            $quantity = (float) $totalBuyQuantity - (float) $totalSellQuantity;

            // récupérer le prix courant via CryptoService (fallbacks inside)
            $currentPrice = $this->cryptoService->getCurrentPrice(
                \App\Models\CryptoCurrency::find($portfolio->crypto_currency_id)->symbol
            ) ?? 0.0;
            $currentValue = $quantity * $currentPrice;

            $investedValue = (float) $portfolio->total_crypto_value;
            $gainLoss = $currentValue - $investedValue;
            $gainLossPercent = $investedValue > 0 ? ($gainLoss / $investedValue) * 100 : null;

            // Append computed fields
            $portfolio->quantity = $quantity;
            $portfolio->current_price = $currentPrice;
            $portfolio->current_value = round($currentValue, 8);
            $portfolio->invested_value = round($investedValue, 8);
            $portfolio->gain_loss = round($gainLoss, 8);
            $portfolio->gain_loss_percent = $gainLossPercent !== null ? round($gainLossPercent, 2) : null;

            return $portfolio;
        })->values();
    }
}
