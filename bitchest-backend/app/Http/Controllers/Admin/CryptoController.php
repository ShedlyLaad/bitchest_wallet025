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
        // Support timeframe: 4h, 1d, 7d, 30d (default: 30d)
        $timeframe = $request->query('timeframe', '30d');
        
        // Convert timeframe to days
        $daysMap = [
            '4h' => 1, // Last 24 hours, but we'll sample 4h intervals
            '1d' => 1,
            '7d' => 7,
            '30d' => 30,
            '90d' => 90
        ];
        
        $days = $daysMap[$timeframe] ?? 30;
        return response()->json($cryptoService->getHistoricalPrices($symbol, $days));
    }
}
