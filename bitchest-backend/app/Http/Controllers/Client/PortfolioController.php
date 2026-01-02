<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Services\PortfolioService;
use App\Services\NotificationService;

class PortfolioController extends Controller
{
    public function __construct(
        private PortfolioService $portfolioService,
        private NotificationService $notificationService
    ){}

    public function index()
    {
        $user = auth()->user();
        $portfolios = $this->portfolioService->getUserPortfolio($user);
        
        // Vérifier et créer des notifications pour les changements de profit/loss
        // Faire cela en arrière-plan pour ne pas ralentir la réponse
        try {
            $this->notificationService->checkAndCreatePortfolioNotifications($user);
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas faire échouer la requête
            \Log::error('Error checking notifications: ' . $e->getMessage());
        }
        
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
        $details = $this->portfolioService->getPurchaseDetails($user, $cryptoCurrencyId);
        
        return response()->json([
            'purchases' => $details
        ]);
    }
}
