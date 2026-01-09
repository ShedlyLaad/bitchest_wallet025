<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RedisPriceService;
use App\Services\CryptoService;
use App\Models\CryptoCurrency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CryptoController extends Controller
{
    public function index(RedisPriceService $redisPriceService, CryptoService $cryptoService)
    {
        try {
            // Priorité 1: Redis (ultra-rapide même pour Admin)
            $prices = $redisPriceService->getAllPrices();
            
            // Si Redis est vide, utiliser CryptoService
            if ($prices->isEmpty()) {
                Log::info('[AdminCryptoController] Redis vide, utilisation du fallback CryptoService');
                $prices = $cryptoService->getCurrentPrices(true);
                
                // Normaliser le format
                $prices = $prices->map(function ($crypto) {
                    $data = is_array($crypto) ? $crypto : (array) $crypto;
                    return [
                        'id' => $data['id'] ?? null,
                        'symbol' => $data['symbol'] ?? '',
                        'name' => $data['name'] ?? '',
                        'price' => isset($data['price']) ? (float) $data['price'] : 0.0,
                        'change24h' => isset($data['change24h']) ? (float) $data['change24h'] : 0.0,
                        'marketCap' => isset($data['marketCap']) ? (float) $data['marketCap'] : 0.0,
                        'volume24h' => isset($data['volume24h']) ? (float) $data['volume24h'] : 0.0,
                    ];
                });
            }
            
            // Format de retour normalisé avec validation et formatage strict
            return response()->json($prices->map(function ($crypto) {
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
            })->values());
            
        } catch (\Exception $e) {
            Log::error('[AdminCryptoController] Erreur', [
                'error' => $e->getMessage()
            ]);
            
            // Fallback final: retourner les cryptos depuis la DB avec des valeurs par défaut
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
            })->values(), 200);
        }
    }

    public function generate(CryptoService $cryptoService)
    {
        $cryptoService->generateInitialPrices();
        return response()->json(['message' => 'Initial prices generated.']);
    }

    public function history($symbol, Request $request, CryptoService $cryptoService)
    {
        // Support timeframe: 1d, 7d, 30d, 90d (default: 7d)
        $timeframe = $request->query('timeframe', '7d');
        
        // Convert timeframe to days
        $daysMap = [
            '1d' => 1,
            '7d' => 7,
            '30d' => 30,
            '90d' => 90
        ];
        
        $days = $daysMap[$timeframe] ?? 7;
        $prices = $cryptoService->getHistoricalPrices($symbol, $days);
        
        // Ensure we return data in the expected format with recorded_at
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
}
