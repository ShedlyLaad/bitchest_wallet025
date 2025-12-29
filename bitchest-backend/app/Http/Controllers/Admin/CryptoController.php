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

    public function history($symbol, CryptoService $cryptoService)
    {
        return response()->json($cryptoService->getHistoricalPrices($symbol, 90));
    }
}
