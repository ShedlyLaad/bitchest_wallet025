<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CryptoService;
use Illuminate\Http\Request;

class CryptoController extends Controller
{
    public function index(CryptoService $cryptoService)
    {
        return response()->json($cryptoService->getCurrentPrices());
    }

    public function generate(CryptoService $cryptoService)
    {
        $cryptoService->generateInitialPrices();
        return response()->json(['message' => 'Initial prices generated.']);
    }

    public function history($symbol, Request $request, CryptoService $cryptoService)
    {
        // Support timeframe: 1d, 7d, 30d, 90d (default: 30d)
        $timeframe = $request->query('timeframe', '30d');
        
        // Convert timeframe to days
        $daysMap = [
            '1d' => 1,
            '7d' => 7,
            '30d' => 30,
            '90d' => 90
        ];
        
        $days = $daysMap[$timeframe] ?? 30;
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
