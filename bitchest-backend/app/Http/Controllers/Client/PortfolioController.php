<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Services\PortfolioService;

class PortfolioController extends Controller
{
    public function __construct(
        private PortfolioService $portfolioService
    ){}

    public function index()
    {
        $user = auth()->user();
        $portfolios = $this->portfolioService->getUserPortfolio($user);
        
        return response()->json([
            'balance' => (float) ($user->euro_balance ?? 0.0),
            'portfolio' => $portfolios
        ]);
    }
    
    public function purchaseDetails($cryptoCurrencyId)
    {
        $user = auth()->user();
        
        $crypto = \App\Models\CryptoCurrency::find($cryptoCurrencyId);
        if (!$crypto) {
            return response()->json([
                'message' => 'Cryptocurrency not found.',
                'crypto_currency_id' => $cryptoCurrencyId
            ], 404);
        }
        
        $details = $this->portfolioService->getPurchaseDetails($user, $cryptoCurrencyId);
        
        return response()->json([
            'purchases' => $details
        ]);
    }
}
