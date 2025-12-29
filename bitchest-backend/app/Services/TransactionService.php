<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\CryptoCurrency;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    private PortfolioService $portfolioService;

    public function __construct(PortfolioService $portfolioService)
    {
        $this->portfolioService = $portfolioService;
    }

    public function processTransaction(User $user, CryptoCurrency $crypto, float $quantity, float $price, string $type)
    {
        return DB::transaction(function () use ($user, $crypto, $quantity, $price, $type) {

            // Re-fetch user with row lock to avoid race conditions on balance updates
            $user = \App\Models\User::where('id', $user->id)->lockForUpdate()->first();

            $amount = (float) $quantity * (float) $price;

            // --- Trouver ou créer le portfolio pour cette crypto ---
            $portfolio = \App\Models\Portfolio::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'crypto_currency_id' => $crypto->id,
                ],
                [
                    'total_crypto_value' => 0, // On n'a plus d'euro_balance ici
                ]
            );

            // --- Vérification du solde pour les achats ---
            if ($type === 'buy') {
                $currentBalance = (float) ($user->euro_balance ?? 0.0);

                if ($currentBalance < $amount) {
                    throw new \InvalidArgumentException("Solde insuffisant. Solde disponible : {$currentBalance} EUR.");
                }

                // Débiter le solde utilisateur
                $user->euro_balance = $currentBalance - $amount;
                $user->save();
            }

            // --- Vérification quantité pour les ventes ---
            if ($type === 'sell') {
                // Calculer la quantité totale détenue
                $totalBuyQuantity = Transaction::where('portfolio_id', $portfolio->id)
                    ->where('type', 'buy')
                    ->sum('quantity');
                $totalSellQuantity = Transaction::where('portfolio_id', $portfolio->id)
                    ->where('type', 'sell')
                    ->sum('quantity');
                $totalQuantity = (float) $totalBuyQuantity - (float) $totalSellQuantity;

                if ($totalQuantity < $quantity) {
                    throw new \InvalidArgumentException("Quantité insuffisante pour la vente. Vous possédez seulement {$totalQuantity}.");
                }

                // Créditer le solde utilisateur
                $currentBalance = (float) ($user->euro_balance ?? 0.0);
                $user->euro_balance = $currentBalance + $amount;
                $user->save();
            }

            // --- création transaction ---
            $tx = Transaction::create([
                'portfolio_id'        => $portfolio->id,
                'type'                => $type,
                'quantity'            => $quantity,
                'price_at_transaction'=> $price,
                'euro_amount'         => $amount,
            ]);

            // --- MAJ portefeuille ---
            $this->portfolioService->updatePortfolio(
                $portfolio,
                $tx,
                $quantity,
                $price,
                $type
            );

            return $tx;
        });
    }
}
