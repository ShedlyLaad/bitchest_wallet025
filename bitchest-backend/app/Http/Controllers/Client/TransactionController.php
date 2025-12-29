<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\BuyCryptoRequest;
use App\Http\Requests\Client\SellCryptoRequest;
use App\Models\CryptoCurrency;
use App\Models\CryptoPrice;
use App\Models\Transaction as TransactionModel;
use App\Models\Portfolio;
use Illuminate\Http\Request;
use App\Services\CryptoService;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;

class TransactionController extends Controller
{
    private CryptoService $cryptoService;
    private TransactionService $transactionService;

    public function __construct(CryptoService $cryptoService, TransactionService $transactionService)
    {
        $this->cryptoService = $cryptoService;
        $this->transactionService = $transactionService;
    }

    public function buy(BuyCryptoRequest $request)
    {
        $user   = auth()->user();
        $crypto = CryptoCurrency::where('symbol', $request->symbol)
            ->where('is_active', true)
            ->firstOrFail();
        $price = $this->cryptoService->getCurrentPrice($crypto->symbol);

        if ($price === null) {
            Log::error('No price available for crypto', ['user_id' => $user->id ?? null, 'symbol' => $crypto->symbol]);
            return response()->json(['message' => 'Crypto introuvable ou inactive.'], 400);
        }

        // Re-fetch fresh user balance
        $freshUser = \App\Models\User::find($user->id);

        $amount = (float) $request->quantity * $price;

        // Pre-validate buy: ensure euro_balance sufficient
        if ($request->isMethod('post') || $request->has('quantity')) {
            if ($request->route()->getActionMethod() === 'buy') {
                $currentBalance = (float) ($freshUser->euro_balance ?? 0.0);
                if ($currentBalance < $amount) {
                    return response()->json(['message' => "Solde insuffisant. Solde disponible : {$currentBalance} EUR."], 400);
                }
            }
        }

        try {
            $tx = $this->transactionService->processTransaction(
                $user,
                $crypto,
                $request->quantity,
                $price,
                'buy'
            );

            $fresh = \App\Models\User::find($user->id);

            return response()->json([
                'message' => 'Achat effectué avec succès',
                'transaction' => $tx,
                'balance' => (float) ($fresh->euro_balance ?? 0.0),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            \Log::error('Transaction error', ['message' => $e->getMessage(), 'user_id' => $user->id]);
            return response()->json(['message' => 'Erreur serveur lors de la transaction.'], 500);
        }
    }

    public function sell(SellCryptoRequest $request)
    {
        $user   = auth()->user();
        $crypto = CryptoCurrency::where('symbol', $request->symbol)
            ->where('is_active', true)
            ->firstOrFail();
        $price = $this->cryptoService->getCurrentPrice($crypto->symbol);

        if ($price === null) {
            Log::error('No price available for crypto', ['user_id' => $user->id ?? null, 'symbol' => $crypto->symbol]);
            return response()->json(['message' => 'Crypto introuvable ou inactive.'], 400);
        }

        // Pre-validate sell: ensure user holds enough quantity
        $portfolio = Portfolio::firstOrCreate([
            'user_id' => $user->id,
            'crypto_currency_id' => $crypto->id,
        ], ['total_crypto_value' => 0]);

        $totalBuyQuantity = TransactionModel::where('portfolio_id', $portfolio->id)
            ->where('type', 'buy')
            ->sum('quantity');
        $totalSellQuantity = TransactionModel::where('portfolio_id', $portfolio->id)
            ->where('type', 'sell')
            ->sum('quantity');
        $totalQuantity = (float) $totalBuyQuantity - (float) $totalSellQuantity;

        if ($totalQuantity < (float) $request->quantity) {
            return response()->json(['message' => "Quantité insuffisante pour la vente. Vous possédez seulement {$totalQuantity}."], 400);
        }

        try {
            $tx = $this->transactionService->processTransaction(
                $user,
                $crypto,
                $request->quantity,
                $price,
                'sell'
            );

            $fresh = \App\Models\User::find($user->id);

            return response()->json([
                'message' => 'Vente confirmée',
                'transaction' => $tx,
                'balance' => (float) ($fresh->euro_balance ?? 0.0),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            \Log::error('Transaction error', ['message' => $e->getMessage(), 'user_id' => $user->id]);
            return response()->json(['message' => 'Erreur serveur lors de la transaction.'], 500);
        }
    }

    /**
     * Retourne l'historique des transactions pour l'utilisateur authentifié
     */
    public function history(Request $request)
    {
        $user = auth()->user();

        $query = Transaction::with(['portfolio.crypto'])
            ->whereHas('portfolio', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });

        $perPage = (int) $request->input('per_page', 50);

        $transactions = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($transactions);
    }
}
