<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\BuyCryptoRequest;
use App\Http\Requests\Client\SellCryptoRequest;
use App\DTOs\TradeOrderData;
use App\Events\BuyExecuted;
use App\Events\SellExecuted;
use App\Models\CryptoCurrency;
use Illuminate\Http\Request;
use App\Services\AccountCacheService;
use App\Services\RedisPriceService;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    private RedisPriceService $redisPriceService;
    private AccountCacheService $accountCacheService;

    public function __construct(RedisPriceService $redisPriceService, AccountCacheService $accountCacheService)
    {
        $this->redisPriceService = $redisPriceService;
        $this->accountCacheService = $accountCacheService;
    }

    public function buy(BuyCryptoRequest $request)
    {
        $user   = auth()->user();
        $crypto = CryptoCurrency::where('symbol', $request->symbol)
            ->where('is_active', true)
            ->firstOrFail();
        $priceData = $this->redisPriceService->getPrice($crypto->symbol);
        $price = $priceData && isset($priceData['price']) ? (float) $priceData['price'] : null;

        if ($price === null) {
            Log::error('No price available for crypto', ['user_id' => $user->id ?? null, 'symbol' => $crypto->symbol]);
            return response()->json(['message' => 'Crypto introuvable ou inactive.'], 400);
        }

        $amount = (float) $request->quantity * $price;
        $currentBalance = $this->accountCacheService->getBalance($user->id, (float) ($user->euro_balance ?? 0.0));

        if ($currentBalance < $amount) {
            return response()->json(['message' => "Solde insuffisant. Solde disponible : {$currentBalance} EUR."], 400);
        }

        $clientReference = (string) Str::uuid();
        event(new BuyExecuted(new TradeOrderData(
            $clientReference,
            $user->id,
            $crypto->id,
            $crypto->symbol,
            (float) $request->quantity,
            $price,
            'buy'
        )));

        return response()->json([
            'message' => 'Achat en cours de traitement',
            'transaction' => [
                'id' => 0,
                'portfolio_id' => 0,
                'type' => 'buy',
                'quantity' => (float) $request->quantity,
                'price_at_transaction' => $price,
                'euro_amount' => $amount,
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString(),
            ],
            'balance' => (float) ($currentBalance - $amount),
            'reference' => $clientReference,
        ], 202);
    }

    public function sell(SellCryptoRequest $request)
    {
        $user   = auth()->user();
        $crypto = CryptoCurrency::where('symbol', $request->symbol)
            ->where('is_active', true)
            ->firstOrFail();
        $priceData = $this->redisPriceService->getPrice($crypto->symbol);
        $price = $priceData && isset($priceData['price']) ? (float) $priceData['price'] : null;

        if ($price === null) {
            Log::error('No price available for crypto', ['user_id' => $user->id ?? null, 'symbol' => $crypto->symbol]);
            return response()->json(['message' => 'Crypto introuvable ou inactive.'], 400);
        }

        $availableQuantity = $this->accountCacheService->getCryptoQuantityOrCompute($user->id, $crypto->id);

        if ($availableQuantity < (float) $request->quantity) {
            return response()->json(['message' => "Quantité insuffisante pour la vente. Vous possédez seulement {$availableQuantity}."], 400);
        }

        $clientReference = (string) Str::uuid();
        event(new SellExecuted(new TradeOrderData(
            $clientReference,
            $user->id,
            $crypto->id,
            $crypto->symbol,
            (float) $request->quantity,
            $price,
            'sell'
        )));

        $amount = (float) $request->quantity * $price;

        return response()->json([
            'message' => 'Vente en cours de traitement',
            'transaction' => [
                'id' => 0,
                'portfolio_id' => 0,
                'type' => 'sell',
                'quantity' => (float) $request->quantity,
                'price_at_transaction' => $price,
                'euro_amount' => $amount,
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString(),
            ],
            'balance' => (float) ($this->accountCacheService->getBalance($user->id, (float) ($user->euro_balance ?? 0.0)) + $amount),
            'reference' => $clientReference,
        ], 202);
    }

    /**
     * Retourne l'historique des transactions pour l'utilisateur authentifié
     * Utilise Redis cache pour un affichage instantané
     */
    public function history(Request $request)
    {
        $user = auth()->user();
        $perPage = (int) $request->input('per_page', 50);
        $page = (int) $request->input('page', 1);

        // Clé de cache unique pour cette requête
        $cacheKey = "transaction:user:{$user->id}:history:page:{$page}:perpage:{$perPage}";
        
        // TTL de cache : 2 minutes pour l'historique
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
