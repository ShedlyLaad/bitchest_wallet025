<?php

namespace App\Jobs;

use App\DTOs\TradeOrderData;
use App\Events\TransactionCreated;
use App\Models\CryptoCurrency;
use App\Models\Portfolio;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AccountCacheService;
use App\Services\PortfolioService;
use App\Services\TransactionCacheService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessTradeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public TradeOrderData $order)
    {
    }

    public function handle(
        PortfolioService $portfolioService,
        AccountCacheService $accountCache,
        TransactionCacheService $transactionCache
    ): void {
        $order = $this->order;

        $transaction = DB::transaction(function () use ($order, $portfolioService) {
            $user = User::whereKey($order->userId)->lockForUpdate()->firstOrFail();
            $crypto = CryptoCurrency::findOrFail($order->cryptoId);

            $portfolio = Portfolio::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'crypto_currency_id' => $crypto->id,
                ],
                [
                    'total_crypto_value' => 0,
                ]
            );

            $amount = $order->amount();

            if ($order->type === 'buy') {
                if ((float) $user->euro_balance < $amount) {
                    throw new \InvalidArgumentException('Solde insuffisant.');
                }
                $user->euro_balance = (float) $user->euro_balance - $amount;
                $user->save();
            }

            if ($order->type === 'sell') {
                $totalBuy = (float) Transaction::where('portfolio_id', $portfolio->id)
                    ->where('type', 'buy')
                    ->sum('quantity');
                $totalSell = (float) Transaction::where('portfolio_id', $portfolio->id)
                    ->where('type', 'sell')
                    ->sum('quantity');
                $totalQuantity = $totalBuy - $totalSell;

                if ($totalQuantity < $order->quantity) {
                    throw new \InvalidArgumentException('Quantité insuffisante.');
                }

                $user->euro_balance = (float) $user->euro_balance + $amount;
                $user->save();
            }

            $transaction = Transaction::create([
                'portfolio_id' => $portfolio->id,
                'type' => $order->type,
                'quantity' => $order->quantity,
                'price_at_transaction' => $order->price,
                'euro_amount' => $amount,
            ]);

            $portfolioService->updatePortfolio($portfolio, $transaction, $order->quantity, $order->price, $order->type);

            return [
                'transaction' => $transaction,
                'portfolio' => $portfolio,
                'user_balance' => (float) $user->euro_balance,
                'crypto' => $crypto,
            ];
        });

        $tx = $transaction['transaction'];
        $portfolio = $transaction['portfolio'];
        $crypto = $transaction['crypto'];

        $totalBuy = (float) Transaction::where('portfolio_id', $portfolio->id)
            ->where('type', 'buy')
            ->sum('quantity');
        $totalSell = (float) Transaction::where('portfolio_id', $portfolio->id)
            ->where('type', 'sell')
            ->sum('quantity');
        $currentQuantity = max(0.0, $totalBuy - $totalSell);

        $accountCache->setBalance($this->order->userId, $transaction['user_balance']);
        $accountCache->setCryptoQuantity($this->order->userId, $crypto->id, $currentQuantity);

        $transactionCache->store($this->order->userId, [
            'cache_id' => $this->order->clientReference,
            'id' => $tx->id,
            'portfolio_id' => $tx->portfolio_id,
            'type' => $tx->type,
            'quantity' => (float) $tx->quantity,
            'price_at_transaction' => (float) $tx->price_at_transaction,
            'euro_amount' => (float) $tx->euro_amount,
            'created_at' => $tx->created_at->toISOString(),
            'updated_at' => $tx->updated_at->toISOString(),
            'portfolio' => [
                'crypto' => [
                    'id' => $crypto->id,
                    'symbol' => $crypto->symbol,
                    'name' => $crypto->name,
                ],
            ],
        ]);

        event(new TransactionCreated(
            $this->order->userId,
            $tx->id,
            $tx->type,
            $crypto->symbol,
            (float) $tx->quantity,
            (float) $tx->euro_amount,
            (float) $tx->price_at_transaction
        ));
    }
}
