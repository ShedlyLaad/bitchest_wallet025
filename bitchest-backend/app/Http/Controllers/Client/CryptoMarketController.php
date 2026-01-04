<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\CryptoService;
use App\Models\CryptoCurrency;
use Illuminate\Support\Facades\Cache;

class CryptoMarketController extends Controller
{
    private CryptoService $cryptoService;

    public function __construct(CryptoService $cryptoService)
    {
        $this->cryptoService = $cryptoService;
    }

    public function index()
    {
        // Utiliser le même service que Admin pour garantir l'identité des valeurs
        $prices = $this->cryptoService->getCurrentPrices();
        
        // Normaliser le format de retour pour garantir la cohérence avec Admin
        return response()->json($prices->map(function ($crypto) {
            return [
                'id' => $crypto['id'] ?? null,
                'symbol' => $crypto['symbol'] ?? '',
                'name' => $crypto['name'] ?? '',
                'price' => isset($crypto['price']) ? (float) $crypto['price'] : 0.0,
                'change24h' => isset($crypto['change24h']) ? (float) $crypto['change24h'] : 0.0,
                'marketCap' => isset($crypto['marketCap']) ? (float) $crypto['marketCap'] : 0.0,
                'volume24h' => isset($crypto['volume24h']) ? (float) $crypto['volume24h'] : 0.0,
            ];
        })->values());
    }

    public function history($crypto_currency_id)
    {
        // Support both ID and symbol for backward compatibility
        $crypto = CryptoCurrency::where('id', $crypto_currency_id)
            ->orWhere('symbol', strtoupper($crypto_currency_id))
            ->first();
        
        if (!$crypto) {
            return response()->json(['error' => 'Cryptocurrency not found'], 404);
        }

        $prices = $this->cryptoService->getHistoricalPrices($crypto->symbol, 30);
        
        // Format response to match frontend expectations
        return response()->json($prices->map(function ($price) {
            return [
                'crypto_currency_id' => $price->crypto_currency_id ?? 0,
                'price' => (float) $price->price,
                'recorded_at' => $price->recorded_at instanceof \Carbon\Carbon 
                    ? $price->recorded_at->toIso8601String() 
                    : (string) $price->recorded_at,
            ];
        })->values());
    }

    /**
     * API REST pour utilisateurs - données Coinbase avec cache pour éviter le rate limit
     */
    public function userCryptos()
    {
        // Cache de 5 minutes pour permettre des mises à jour plus fréquentes tout en évitant le rate limit
        $cacheKey = 'user_cryptos_coinbase';
        
        $prices = Cache::remember($cacheKey, 300, function () {
            return $this->cryptoService->getCurrentPrices();
        });
        
        // Normaliser le format de retour pour garantir la cohérence avec Admin
        return response()->json($prices->map(function ($crypto) {
            return [
                'id' => $crypto['id'] ?? null,
                'symbol' => $crypto['symbol'] ?? '',
                'name' => $crypto['name'] ?? '',
                'price' => isset($crypto['price']) ? (float) $crypto['price'] : 0.0,
                'change24h' => isset($crypto['change24h']) ? (float) $crypto['change24h'] : 0.0,
                'marketCap' => isset($crypto['marketCap']) ? (float) $crypto['marketCap'] : 0.0,
                'volume24h' => isset($crypto['volume24h']) ? (float) $crypto['volume24h'] : 0.0,
            ];
        })->values());
    }
}
