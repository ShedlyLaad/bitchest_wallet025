<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\CryptoCurrency;
use App\Models\Portfolio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    private const MIN_QUANTITY = 0.00000001;
    
    public function __construct(
        private PortfolioService $portfolioService,
        private NotificationService $notificationService
    ) {}

    public function processTransaction(
        User $user,
        CryptoCurrency $crypto,
        float $quantity,
        float $price,
        string $type
    ): Transaction {
        $this->validateTransactionParams($quantity, $price, $type);
        
        return DB::transaction(function () use ($user, $crypto, $quantity, $price, $type) {
            $user = User::where('id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();
            
            $amount = $quantity * $price;
            $portfolio = $this->findOrCreatePortfolio($user->id, $crypto->id);
            
            if ($type === 'buy') {
                $this->processBuy($user, $amount);
            } else {
                $this->processSell($user, $portfolio, $quantity, $amount);
            }
            
            $transaction = $this->createTransaction($portfolio, $type, $quantity, $price, $amount);
            
            $this->portfolioService->updatePortfolio(
                $portfolio,
                $transaction,
                $quantity,
                $price,
                $type
            );
            
            $this->notificationService->checkAndCreatePortfolioNotifications($user);
            
            return $transaction;
        });
    }
    
    private function validateTransactionParams(float $quantity, float $price, string $type): void
    {
        if ($quantity < self::MIN_QUANTITY) {
            throw new \InvalidArgumentException("Quantity must be greater than " . self::MIN_QUANTITY);
        }
        
        if ($price <= 0) {
            throw new \InvalidArgumentException("Price must be greater than 0");
        }
        
        if (!in_array($type, ['buy', 'sell'])) {
            throw new \InvalidArgumentException("Transaction type must be 'buy' or 'sell'");
        }
    }
    
    private function findOrCreatePortfolio(int $userId, int $cryptoId): Portfolio
    {
        return Portfolio::firstOrCreate(
            [
                'user_id' => $userId,
                'crypto_currency_id' => $cryptoId,
            ],
            [
                'total_crypto_value' => 0,
            ]
        );
    }
    
    private function processBuy(User $user, float $amount): void
    {
        $currentBalance = (float) ($user->euro_balance ?? 0.0);
        
        if ($currentBalance < $amount) {
            throw new \InvalidArgumentException(
                "Insufficient balance. Available balance: " . number_format($currentBalance, 2) . " EUR."
            );
        }
        
        $user->euro_balance = $currentBalance - $amount;
        $user->save();
    }
    
    private function processSell(User $user, Portfolio $portfolio, float $quantity, float $amount): void
    {
        $totalBuyQuantity = Transaction::getCachedQuantity($portfolio->id, 'buy');
        $totalSellQuantity = Transaction::getCachedQuantity($portfolio->id, 'sell');
        $availableQuantity = $totalBuyQuantity - $totalSellQuantity;
        
        if ($availableQuantity < $quantity) {
            throw new \InvalidArgumentException(
                "Insufficient quantity for sale. You only own " . 
                number_format($availableQuantity, 8) . "."
            );
        }
        
        $currentBalance = (float) ($user->euro_balance ?? 0.0);
        $user->euro_balance = $currentBalance + $amount;
        $user->save();
    }
    
    private function createTransaction(
        Portfolio $portfolio,
        string $type,
        float $quantity,
        float $price,
        float $amount
    ): Transaction {
        return Transaction::create([
            'portfolio_id' => $portfolio->id,
            'type' => $type,
            'quantity' => $quantity,
            'price_at_transaction' => $price,
            'euro_amount' => $amount,
        ]);
    }
}
