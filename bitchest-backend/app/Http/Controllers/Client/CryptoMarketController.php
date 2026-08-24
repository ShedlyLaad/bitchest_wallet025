<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\RedisPriceService;
use App\Services\CryptoService;
use App\Models\CryptoCurrency;
use Illuminate\Support\Facades\Log;

class CryptoMarketController extends Controller
{
    private RedisPriceService $redisPriceService;
    private CryptoService $cryptoService;

    public function __construct(RedisPriceService $redisPriceService, CryptoService $cryptoService)
    {
        $this->redisPriceService = $redisPriceService;
        $this->cryptoService = $cryptoService;
    }

    /**
     * GET /api/market ou /api/public/market
     * Lecture ultra-rapide depuis Redis avec fallback automatique vers DB
     * Route publique accessible sans authentification
     */
    public function index()
    {
        try {
            // Priorité 1: Redis (ultra-rapide < 5ms)
            $prices = $this->redisPriceService->getAllPrices();
            
            if ($prices->isEmpty()) {
                Log::info('[CryptoMarketController] Redis vide, fallback DB');
            }
            
            // Format de retour normalisé avec validation et formatage strict
            $formatted = $prices->map(function ($crypto) {
                $data = is_array($crypto) ? $crypto : (array) $crypto;
                
                // Normaliser et valider le prix
                $price = isset($data['price']) && !is_null($data['price']) && is_numeric($data['price'])
                    ? max(0.0, (float) $data['price'])
                    : 0.0;
                
                // Normaliser et valider change24h (limiter entre -99 et +200)
                $change24h = isset($data['change24h']) && !is_null($data['change24h']) && is_numeric($data['change24h'])
                    ? max(-99.0, min(200.0, round((float) $data['change24h'], 2)))
                    : 0.0;
                
                return [
                    'id' => $data['id'] ?? null,
                    'symbol' => strtoupper($data['symbol'] ?? ''),
                    'name' => $data['name'] ?? '',
                    'price' => $price,
                    'change24h' => $change24h,
                    'marketCap' => isset($data['marketCap']) && is_numeric($data['marketCap'])
                        ? max(0.0, (float) $data['marketCap'])
                        : 0.0,
                    'volume24h' => isset($data['volume24h']) && is_numeric($data['volume24h'])
                        ? max(0.0, (float) $data['volume24h'])
                        : 0.0,
                ];
            })->filter(function ($crypto) {
                // Filtrer les cryptos invalides
                return !empty($crypto['symbol']) && $crypto['price'] > 0;
            })->values();
            
            // Si on a des données, les retourner
            if ($formatted->isNotEmpty()) {
                return response()->json($formatted->toArray(), 200);
            }
            
            // Fallback: retourner les cryptos depuis la DB avec des valeurs par défaut
            $cryptos = CryptoCurrency::where('is_active', true)->get();
            return response()->json($cryptos->map(function ($crypto) {
                return [
                    'id' => $crypto->id,
                    'symbol' => strtoupper($crypto->symbol),
                    'name' => $crypto->name,
                    'price' => 0.0,
                    'change24h' => 0.0,
                    'marketCap' => 0.0,
                    'volume24h' => 0.0,
                ];
            })->values()->toArray(), 200);
            
        } catch (\Exception $e) {
            Log::error('[CryptoMarketController] Erreur', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback final: retourner les cryptos depuis la DB avec des valeurs par défaut
            try {
                $cryptos = CryptoCurrency::where('is_active', true)->get();
                return response()->json($cryptos->map(function ($crypto) {
                    return [
                        'id' => $crypto->id,
                        'symbol' => strtoupper($crypto->symbol),
                        'name' => $crypto->name,
                        'price' => 0.0,
                        'change24h' => 0.0,
                        'marketCap' => 0.0,
                        'volume24h' => 0.0,
                    ];
                })->values()->toArray(), 200);
            } catch (\Exception $dbError) {
                Log::error('[CryptoMarketController] Erreur DB fallback', [
                    'error' => $dbError->getMessage()
                ]);
                return response()->json([], 200);
            }
        }
    }

    public function history($crypto_currency_id, \Illuminate\Http\Request $request)
    {
        // Support both ID and symbol for backward compatibility
        $crypto = CryptoCurrency::where('id', $crypto_currency_id)
            ->orWhere('symbol', strtoupper($crypto_currency_id))
            ->first();

        if (!$crypto) {
            return response()->json(['error' => 'Cryptocurrency not found'], 404);
        }

        // Support timeframe: 1d, 7d, 30d, 90d (default: 30d, same as before)
        $timeframe = $request->query('timeframe', '30d');
        $daysMap = [
            '1d' => 1,
            '7d' => 7,
            '30d' => 30,
            '90d' => 90
        ];
        $days = $daysMap[$timeframe] ?? 30;

        $prices = $this->cryptoService->getHistoricalPrices($crypto->symbol, $days, false);
        
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
     * GET /api/user/cryptos
     * Alias de index() - Utilise Redis avec fallback
     */
    public function userCryptos()
    {
        // Même logique que index() - Redis avec fallback
        return $this->index();
    }
}
