<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\BuyCryptoRequest;
use App\Http\Requests\Client\SellCryptoRequest;
use App\Events\TransactionCreated;
use App\Models\CryptoCurrency;
use Illuminate\Http\Request;
use App\Services\AccountCacheService;
use App\Services\RedisPriceService;
use App\Services\TransactionService;
use App\Services\TransactionCacheService;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;

class TransactionController extends Controller
{
    private RedisPriceService $redisPriceService;
    private AccountCacheService $accountCacheService;
    private TransactionService $transactionService;
    private TransactionCacheService $transactionCacheService;

    public function __construct(
        RedisPriceService $redisPriceService,
        AccountCacheService $accountCacheService,
        TransactionService $transactionService,
        TransactionCacheService $transactionCacheService
    ) {
        $this->redisPriceService = $redisPriceService;
        $this->accountCacheService = $accountCacheService;
        $this->transactionService = $transactionService;
        $this->transactionCacheService = $transactionCacheService;
    }

    public function buy(BuyCryptoRequest $request)
    {
        $user = auth()->user();
        
        $crypto = CryptoCurrency::where('symbol', $request->symbol)
            ->where('is_active', true)
            ->first();
        
        if (!$crypto) {
            return response()->json([
                'message' => 'Cryptocurrency not found or inactive.',
                'symbol' => $request->symbol
            ], 404);
        }
        
        $priceData = $this->redisPriceService->getPrice($crypto->symbol);
        $price = $priceData && isset($priceData['price']) ? (float) $priceData['price'] : null;

        if ($price === null || $price <= 0) {
            Log::error('No price available for crypto', ['user_id' => $user->id ?? null, 'symbol' => $crypto->symbol]);
            return response()->json([
                'message' => 'Price not available for this cryptocurrency. Please try again later.',
                'symbol' => $crypto->symbol
            ], 400);
        }

        try {
            $transaction = $this->transactionService->processTransaction(
                $user,
                $crypto,
                (float) $request->quantity,
                $price,
                'buy'
            );

            $user->refresh();
            $newBalance = (float) $user->euro_balance;

            $this->accountCacheService->setBalance($user->id, $newBalance);
            
            $portfolio = $transaction->portfolio;
            $totalBuy = (float) Transaction::where('portfolio_id', $portfolio->id)
                ->where('type', 'buy')
                ->sum('quantity');
            $totalSell = (float) Transaction::where('portfolio_id', $portfolio->id)
                ->where('type', 'sell')
                ->sum('quantity');
            $currentQuantity = max(0.0, $totalBuy - $totalSell);
            $this->accountCacheService->setCryptoQuantity($user->id, $crypto->id, $currentQuantity);

            $this->transactionCacheService->store($user->id, [
                'id' => $transaction->id,
                'portfolio_id' => $transaction->portfolio_id,
                'type' => $transaction->type,
                'quantity' => (float) $transaction->quantity,
                'price_at_transaction' => (float) $transaction->price_at_transaction,
                'euro_amount' => (float) $transaction->euro_amount,
                'created_at' => $transaction->created_at->toISOString(),
                'updated_at' => $transaction->updated_at->toISOString(),
                'portfolio' => [
                    'crypto' => [
                        'id' => $crypto->id,
                        'symbol' => $crypto->symbol,
                        'name' => $crypto->name,
                    ],
                ],
            ]);

            event(new TransactionCreated(
                $user->id,
                $transaction->id,
                $transaction->type,
                $crypto->symbol,
                (float) $transaction->quantity,
                (float) $transaction->euro_amount,
                (float) $transaction->price_at_transaction
            ));

            $transaction->load('portfolio.crypto');

            return response()->json([
                'message' => 'Purchase completed successfully',
                'transaction' => [
                    'id' => $transaction->id,
                    'portfolio_id' => $transaction->portfolio_id,
                    'type' => $transaction->type,
                    'quantity' => (float) $transaction->quantity,
                    'price_at_transaction' => (float) $transaction->price_at_transaction,
                    'euro_amount' => (float) $transaction->euro_amount,
                    'created_at' => $transaction->created_at->toISOString(),
                    'updated_at' => $transaction->updated_at->toISOString(),
                    'portfolio' => [
                        'crypto' => [
                            'id' => $crypto->id,
                            'symbol' => $crypto->symbol,
                            'name' => $crypto->name,
                        ],
                    ],
                ],
                'balance' => $newBalance,
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            Log::error('Transaction buy error', [
                'user_id' => $user->id,
                'symbol' => $crypto->symbol,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Error during purchase. Please try again.'], 500);
        }
    }

    public function sell(SellCryptoRequest $request)
    {
        $user = auth()->user();
        
        $crypto = CryptoCurrency::where('symbol', $request->symbol)
            ->where('is_active', true)
            ->first();
        
        if (!$crypto) {
            return response()->json([
                'message' => 'Cryptocurrency not found or inactive.',
                'symbol' => $request->symbol
            ], 404);
        }
        
        $priceData = $this->redisPriceService->getPrice($crypto->symbol);
        $price = $priceData && isset($priceData['price']) ? (float) $priceData['price'] : null;

        if ($price === null || $price <= 0) {
            Log::error('No price available for crypto', ['user_id' => $user->id ?? null, 'symbol' => $crypto->symbol]);
            return response()->json([
                'message' => 'Price not available for this cryptocurrency. Please try again later.',
                'symbol' => $crypto->symbol
            ], 400);
        }

        try {
            $transaction = $this->transactionService->processTransaction(
                $user,
                $crypto,
                (float) $request->quantity,
                $price,
                'sell'
            );

            $user->refresh();
            $newBalance = (float) $user->euro_balance;

            $this->accountCacheService->setBalance($user->id, $newBalance);
            
            $portfolio = $transaction->portfolio;
            $totalBuy = (float) Transaction::where('portfolio_id', $portfolio->id)
                ->where('type', 'buy')
                ->sum('quantity');
            $totalSell = (float) Transaction::where('portfolio_id', $portfolio->id)
                ->where('type', 'sell')
                ->sum('quantity');
            $currentQuantity = max(0.0, $totalBuy - $totalSell);
            $this->accountCacheService->setCryptoQuantity($user->id, $crypto->id, $currentQuantity);

            $this->transactionCacheService->store($user->id, [
                'id' => $transaction->id,
                'portfolio_id' => $transaction->portfolio_id,
                'type' => $transaction->type,
                'quantity' => (float) $transaction->quantity,
                'price_at_transaction' => (float) $transaction->price_at_transaction,
                'euro_amount' => (float) $transaction->euro_amount,
                'created_at' => $transaction->created_at->toISOString(),
                'updated_at' => $transaction->updated_at->toISOString(),
                'portfolio' => [
                    'crypto' => [
                        'id' => $crypto->id,
                        'symbol' => $crypto->symbol,
                        'name' => $crypto->name,
                    ],
                ],
            ]);

            event(new TransactionCreated(
                $user->id,
                $transaction->id,
                $transaction->type,
                $crypto->symbol,
                (float) $transaction->quantity,
                (float) $transaction->euro_amount,
                (float) $transaction->price_at_transaction
            ));

            $transaction->load('portfolio.crypto');

            return response()->json([
                'message' => 'Sale completed successfully',
                'transaction' => [
                    'id' => $transaction->id,
                    'portfolio_id' => $transaction->portfolio_id,
                    'type' => $transaction->type,
                    'quantity' => (float) $transaction->quantity,
                    'price_at_transaction' => (float) $transaction->price_at_transaction,
                    'euro_amount' => (float) $transaction->euro_amount,
                    'created_at' => $transaction->created_at->toISOString(),
                    'updated_at' => $transaction->updated_at->toISOString(),
                    'portfolio' => [
                        'crypto' => [
                            'id' => $crypto->id,
                            'symbol' => $crypto->symbol,
                            'name' => $crypto->name,
                        ],
                    ],
                ],
                'balance' => $newBalance,
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            Log::error('Transaction sell error', [
                'user_id' => $user->id,
                'symbol' => $crypto->symbol,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Error during sale. Please try again.'], 500);
        }
    }

    public function history(Request $request)
    {
        $user = auth()->user();
        $perPage = (int) $request->input('per_page', 50);
        $page = (int) $request->input('page', 1);

        $cacheKey = "transaction:user:{$user->id}:history:page:{$page}:perpage:{$perPage}";
        $cacheTTL = 120;

        if ($page === 1) {
            $cachedTransactions = app(\App\Services\TransactionCacheService::class)
                ->getRecent($user->id, $perPage);

            if (!empty($cachedTransactions)) {
                $totalCount = Transaction::whereHas('portfolio', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->count();
                $lastPage = (int) ceil(max(1, $totalCount) / $perPage);

                return response()->json([
                    'data' => $cachedTransactions,
                    'current_page' => 1,
                    'last_page' => $lastPage,
                    'per_page' => $perPage,
                    'total' => $totalCount,
                ]);
            }
        }

        $transactions = \Illuminate\Support\Facades\Cache::remember($cacheKey, $cacheTTL, function () use ($user, $perPage) {
            $query = Transaction::with(['portfolio.crypto'])
                ->whereHas('portfolio', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });

            return $query->orderBy('created_at', 'desc')->paginate($perPage);
        });

        return response()->json($transactions);
    }
}
