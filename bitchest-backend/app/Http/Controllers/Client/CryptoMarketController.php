<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\CryptoService;

class CryptoMarketController extends Controller
{
    private CryptoService $cryptoService;

    public function __construct(CryptoService $cryptoService)
    {
        $this->cryptoService = $cryptoService;
    }

    public function index()
    {
        return $this->cryptoService->getCurrentPrices();
    }

    public function history($symbol)
    {
        return $this->cryptoService->getHistoricalPrices($symbol, 30);
    }
}
