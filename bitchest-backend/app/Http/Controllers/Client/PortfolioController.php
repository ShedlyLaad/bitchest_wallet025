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
        
        // Retourner le solde utilisateur réel et le portfolio enrichi
        return response()->json([
            'balance' => (float) ($user->euro_balance ?? 0.0),
            'portfolio' => $portfolios
        ]);
    }
    
    /**
     * Récupère les détails des achats pour une crypto spécifique
     */
    public function purchaseDetails($cryptoCurrencyId)
    {
        $user = auth()->user();
        
        // Vérifier que la crypto existe
        $crypto = \App\Models\CryptoCurrency::find($cryptoCurrencyId);
        if (!$crypto) {
            return response()->json([
                'message' => 'Cryptomonnaie introuvable.',
                'crypto_currency_id' => $cryptoCurrencyId
            ], 404);
        }
        
        $details = $this->portfolioService->getPurchaseDetails($user, $cryptoCurrencyId);
        
        return response()->json([
            'purchases' => $details
        ]);
    }
}
