<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RedisPriceService;
use App\Services\CryptoService;
use App\Services\CryptoPriceUpdateService;
use App\Services\NotificationService;
use App\Models\CryptoCurrency;
use App\Models\CryptoPriceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CryptoController extends Controller
{
    /**
     * A price is considered "Updated" if it was refreshed within this window.
     * Gives a margin above the 24h scheduler cadence for manual/late runs.
     */
    private const FRESHNESS_WINDOW_HOURS = 26;

    public function index(RedisPriceService $redisPriceService)
    {
        try {
            // Priorité 1: Redis (ultra-rapide même pour Admin)
            $prices = $redisPriceService->getAllPrices();

            if ($prices->isEmpty()) {
                Log::info('[AdminCryptoController] Redis vide, fallback DB');
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

                $updatedAt = $data['updatedAt'] ?? null;

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
                    'updatedAt' => $updatedAt,
                    'status' => $this->resolveStatus($price, $updatedAt),
                ];
            })->filter(function ($crypto) {
                // Filtrer les cryptos invalides
                return !empty($crypto['symbol']) && $crypto['price'] > 0;
            })->values());

        } catch (\Exception $e) {
            Log::error('[AdminCryptoController] Erreur', [
                'error' => $e->getMessage()
            ]);

            // Fallback final: dernier prix connu en DB (jamais une valeur vide/0
            // à la place d'un prix valide existant)
            $cryptos = CryptoCurrency::where('is_active', true)->get();
            return response()->json($cryptos->map(function ($crypto) {
                $latest = CryptoPriceRecord::where('crypto_currency_id', $crypto->id)
                    ->latest('recorded_at')
                    ->first();

                return [
                    'id' => $crypto->id,
                    'symbol' => strtoupper($crypto->symbol),
                    'name' => $crypto->name,
                    'price' => $latest ? max(0.0, (float) $latest->price) : 0.0,
                    'change24h' => 0.0,
                    'marketCap' => 0.0,
                    'volume24h' => 0.0,
                    'updatedAt' => $latest?->recorded_at?->toIso8601String(),
                    'status' => 'Error',
                ];
            })->values(), 200);
        }
    }

    /**
     * Determines the Admin Market display status for a crypto price.
     */
    private function resolveStatus(float $price, ?string $updatedAt): string
    {
        if ($price <= 0) {
            return 'Error';
        }

        if (!$updatedAt) {
            return 'Unknown';
        }

        try {
            $isFresh = Carbon::parse($updatedAt)->greaterThan(now()->subHours(self::FRESHNESS_WINDOW_HOURS));
        } catch (\Exception $e) {
            return 'Unknown';
        }

        return $isFresh ? 'Updated' : 'Outdated';
    }

    public function generate(CryptoService $cryptoService)
    {
        $cryptoService->generateInitialPrices();
        return response()->json(['message' => 'Initial prices generated.']);
    }

    /**
     * Fetches live prices from Coinbase for review (read-only, nothing is
     * persisted). Powers the "Review price update" modal on Admin Market.
     */
    public function preview(CryptoPriceUpdateService $service)
    {
        return response()->json($service->preview());
    }

    /**
     * Applies the price update the admin just reviewed: fetches live prices
     * again and persists them (same logic as crypto:update-prices).
     */
    public function approve(CryptoPriceUpdateService $service, NotificationService $notificationService)
    {
        $result = $service->apply();

        if ($result['status'] === 'busy') {
            return response()->json([
                'message' => 'An update is already running. Please try again shortly.',
            ], 409);
        }

        if ($result['updated'] > 0) {
            $notificationService->notifyAdminsCryptoPricesUpdated($result['updated']);
        }

        return response()->json([
            'message' => "{$result['updated']}/{$result['total']} crypto prices updated.",
            'updated' => $result['updated'],
            'failed' => $result['failed'],
            'total' => $result['total'],
        ]);
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
        $prices = $cryptoService->getHistoricalPrices($symbol, $days, false);
        
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
