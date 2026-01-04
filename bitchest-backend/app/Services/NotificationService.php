<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    private PortfolioService $portfolioService;
    private LevelService $levelService;

    public function __construct(PortfolioService $portfolioService, LevelService $levelService)
    {
        $this->portfolioService = $portfolioService;
        $this->levelService = $levelService;
    }

    /**
     * Génère des notifications pour les changements de profit/loss dans le portfolio
     * Vérifie aussi les montées de niveau
     */
    public function checkAndCreatePortfolioNotifications(User $user): void
    {
        try {
            // Vérifier les montées de niveau en premier
            $this->checkLevelUp($user);
            
            // Ensuite vérifier les notifications de portfolio
            $portfolio = $this->portfolioService->getUserPortfolio($user);
            
            foreach ($portfolio as $position) {
                $this->checkPositionNotifications($user, $position);
            }
        } catch (\Exception $e) {
            Log::error('Error checking portfolio notifications: ' . $e->getMessage());
        }
    }
    
    /**
     * Vérifie si l'utilisateur a monté de niveau et crée une notification
     */
    public function checkLevelUp(User $user): void
    {
        try {
            $result = $this->levelService->updateUserLevel($user);
            
            if ($result['level_up']) {
                $newLevel = $result['new_level'];
                $levelName = $this->levelService->getLevelName($newLevel);
                $xpForNext = $result['xp_for_next_level'];
                $currentXp = $result['new_xp'];
                
                // Vérifier si on a déjà notifié ce niveau récemment (dans les 5 dernières minutes)
                $recentNotification = Notification::where('user_id', $user->id)
                    ->where('type', 'level_up')
                    ->where('level', $newLevel)
                    ->where('created_at', '>=', now()->subMinutes(5))
                    ->first();
                
                if (!$recentNotification) {
                    $this->createLevelUpNotification($user, $newLevel, $levelName, $currentXp, $xpForNext);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error checking level up: ' . $e->getMessage());
        }
    }
    
    /**
     * Crée une notification de montée de niveau
     */
    private function createLevelUpNotification(User $user, int $level, string $levelName, int $currentXp, int $xpForNext): void
    {
        $title = "🎉 Level Up!";
        $message = "Félicitations! Vous avez atteint le niveau {$level} - {$levelName}! Continuez à trader pour monter encore plus haut.";
        
        Notification::create([
            'user_id' => $user->id,
            'type' => 'level_up',
            'title' => $title,
            'message' => $message,
            'level' => $level,
            'level_name' => $levelName,
            'is_read' => false,
        ]);
    }

    /**
     * Vérifie et crée des notifications pour une position spécifique
     */
    private function checkPositionNotifications(User $user, $position): void
    {
        $gainLoss = $position->gain_loss ?? 0;
        $gainLossPercent = $position->gain_loss_percent ?? 0;
        $cryptoSymbol = $position->crypto->symbol ?? 'Unknown';
        $cryptoName = $position->crypto->name ?? 'Unknown';
        
        // Récupérer la dernière notification pour cette crypto
        $lastNotification = Notification::where('user_id', $user->id)
            ->where('crypto_currency_id', $position->crypto_currency_id)
            ->whereIn('type', ['profit', 'loss'])
            ->orderBy('created_at', 'desc')
            ->first();

        // Seuils pour créer des notifications
        $profitThreshold = 50.0; // €50 de profit
        $lossThreshold = -50.0; // €50 de perte
        $percentThreshold = 5.0; // 5% de changement

        // Vérifier si on doit créer une notification de profit
        if ($gainLoss > $profitThreshold || $gainLossPercent > $percentThreshold) {
            // Vérifier si on a déjà notifié récemment (dans les 30 dernières minutes)
            if (!$lastNotification || $lastNotification->created_at->lt(now()->subMinutes(30))) {
                // Vérifier si le gain a augmenté significativement
                if (!$lastNotification || 
                    ($gainLoss > ($lastNotification->gain_loss ?? 0) + $profitThreshold) ||
                    ($gainLossPercent > ($lastNotification->gain_loss_percent ?? 0) + $percentThreshold)) {
                    
                    $this->createNotification($user, $position, 'profit', $gainLoss, $gainLossPercent);
                }
            }
        }

        // Vérifier si on doit créer une notification de perte
        if ($gainLoss < $lossThreshold || $gainLossPercent < -$percentThreshold) {
            // Vérifier si on a déjà notifié récemment
            if (!$lastNotification || $lastNotification->created_at->lt(now()->subMinutes(30))) {
                // Vérifier si la perte a augmenté significativement
                if (!$lastNotification || 
                    ($gainLoss < ($lastNotification->gain_loss ?? 0) + $lossThreshold) ||
                    ($gainLossPercent < ($lastNotification->gain_loss_percent ?? 0) - $percentThreshold)) {
                    
                    $this->createNotification($user, $position, 'loss', $gainLoss, $gainLossPercent);
                }
            }
        }
    }

    /**
     * Crée une notification
     */
    private function createNotification(
        User $user,
        $position,
        string $type,
        float $gainLoss,
        float $gainLossPercent
    ): void {
        $cryptoSymbol = $position->crypto->symbol ?? 'Unknown';
        $cryptoName = $position->crypto->name ?? 'Unknown';
        
        $isProfit = $type === 'profit';
        $sign = $isProfit ? '+' : '';
        $emoji = $isProfit ? '📈' : '📉';
        $color = $isProfit ? 'green' : 'red';
        
        $title = $isProfit 
            ? "Profit sur {$cryptoSymbol}" 
            : "Perte sur {$cryptoSymbol}";
        
        $message = $isProfit
            ? "Votre position en {$cryptoName} ({$cryptoSymbol}) a généré un profit de {$sign}€" . number_format(abs($gainLoss), 2) . " ({$sign}" . number_format(abs($gainLossPercent), 2) . "%)"
            : "Votre position en {$cryptoName} ({$cryptoSymbol}) a subi une perte de {$sign}€" . number_format(abs($gainLoss), 2) . " ({$sign}" . number_format(abs($gainLossPercent), 2) . "%)";

        Notification::create([
            'user_id' => $user->id,
            'portfolio_id' => $position->id,
            'crypto_currency_id' => $position->crypto_currency_id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'crypto_symbol' => $cryptoSymbol,
            'gain_loss' => $gainLoss,
            'gain_loss_percent' => $gainLossPercent,
            'current_price' => $position->current_price ?? 0,
            'previous_price' => null, // On pourrait stocker le prix précédent si nécessaire
            'is_read' => false,
        ]);
    }

    /**
     * Marque une notification comme lue
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * Marque toutes les notifications comme lues pour un utilisateur
     */
    public function markAllAsRead(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
}

