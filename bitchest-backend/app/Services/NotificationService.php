<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Portfolio;
use App\Models\User;
use App\Services\NotificationCacheService;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    private PortfolioService $portfolioService;
    private LevelService $levelService;
    private NotificationCacheService $notificationCacheService;

    public function __construct(PortfolioService $portfolioService, LevelService $levelService, NotificationCacheService $notificationCacheService)
    {
        $this->portfolioService = $portfolioService;
        $this->levelService = $levelService;
        $this->notificationCacheService = $notificationCacheService;
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
            // Recharger l'utilisateur pour avoir les valeurs à jour
            $user->refresh();
            
            $oldLevel = (int) ($user->level ?? 1);
            $oldXp = (int) ($user->experience_points ?? 0);
            
            // Calculer les nouveaux points d'expérience et niveau
            $result = $this->levelService->updateUserLevel($user);
            
            // Recharger l'utilisateur après la mise à jour
            $user->refresh();
            
            $newLevel = $result['new_level'];
            $newXp = $result['new_xp'];
            
            // Vérifier si le niveau a vraiment augmenté
            if ($result['level_up'] && $newLevel > $oldLevel) {
                $levelName = $this->levelService->getLevelName($newLevel);
                $xpForNext = $result['xp_for_next_level'];
                
                // Vérifier si on a déjà notifié ce niveau récemment (dans les 5 dernières minutes)
                $recentNotification = Notification::where('user_id', $user->id)
                    ->where('type', 'level_up')
                    ->where('level', $newLevel)
                    ->where('created_at', '>=', now()->subMinutes(5))
                    ->first();
                
                if (!$recentNotification) {
                    $this->createLevelUpNotification($user, $newLevel, $levelName, $newXp, $xpForNext);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error checking level up: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());
        }
    }
    
    /**
     * Crée une notification de montée de niveau
     */
    private function createLevelUpNotification(User $user, int $level, string $levelName, int $currentXp, int $xpForNext): void
    {
        $title = "🎉 Level Up!";
        $message = "Congratulations! You have reached level {$level} - {$levelName}! Keep trading to level up even higher.";
        
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'level_up',
            'title' => $title,
            'message' => $message,
            'level' => $level,
            'level_name' => $levelName,
            'is_read' => false,
        ]);

        $this->notificationCacheService->store($user->id, $this->normalizeNotification($notification));
        
        // Supprimer les anciennes notifications si on dépasse le maximum (garder les 50 plus récentes)
        $this->cleanupOldNotifications($user->id, 50);
    }

    /**
     * Vérifie et crée des notifications pour une position spécifique
     * Logique simplifiée et plus sensible pour détecter les changements
     */
    private function checkPositionNotifications(User $user, $position): void
    {
        try {
            // Vérifier que la position a des données valides
            if (!$position || !isset($position->crypto) || !$position->crypto) {
                return;
            }
            
            $gainLoss = (float) ($position->gain_loss ?? 0);
            $gainLossPercent = $position->gain_loss_percent !== null ? (float) $position->gain_loss_percent : 0;
            $cryptoSymbol = $position->crypto->symbol ?? 'Unknown';
            $currentPrice = (float) ($position->current_price ?? 0);
            $quantity = (float) ($position->quantity ?? 0);
            
            // Ignorer si la quantité est nulle ou négative
            if ($quantity <= 0) {
                return;
            }
            
            // Récupérer la dernière notification pour cette crypto
            $lastNotification = Notification::where('user_id', $user->id)
                ->where('crypto_currency_id', $position->crypto_currency_id)
                ->whereIn('type', ['profit', 'loss'])
                ->orderBy('created_at', 'desc')
                ->first();

            // Seuils très bas pour tester - à ajuster en production
            $profitThreshold = 0.01; // €0.01 de profit minimum (pour tester)
            $lossThreshold = -0.01; // €0.01 de perte minimum (pour tester)
            $percentThreshold = 0.1; // 0.1% de changement minimum (pour tester)

            // Vérifier si on doit créer une notification de profit
            if ($gainLoss >= $profitThreshold || $gainLossPercent >= $percentThreshold) {
                $shouldNotify = false;
                
                if (!$lastNotification) {
                    // Première notification pour cette crypto si le seuil est dépassé
                    $shouldNotify = true;
                } else {
                    // Vérifier si on a déjà notifié récemment (dans les 15 dernières minutes)
                    $lastNotificationTime = $lastNotification->created_at;
                    $timeSinceLastNotification = now()->diffInMinutes($lastNotificationTime);
                    
                    // Si la dernière notification date de plus de 15 minutes
                    if ($timeSinceLastNotification >= 15) {
                        // Vérifier si c'est toujours un profit (même si pas de changement significatif)
                        $lastGainLoss = (float) ($lastNotification->gain_loss ?? 0);
                        $lastType = $lastNotification->type;
                        
                        // Notifier si :
                        // 1. La dernière notification était une perte et maintenant c'est un profit
                        // 2. OU si le gain a augmenté de 5€ ou plus
                        // 3. OU si le pourcentage a augmenté de 1% ou plus
                        if ($lastType === 'loss' || 
                            ($gainLoss - $lastGainLoss) >= 5.0 ||
                            abs($gainLossPercent - (float)($lastNotification->gain_loss_percent ?? 0)) >= 1.0) {
                            $shouldNotify = true;
                        }
                    }
                }
                
                if ($shouldNotify) {
                    $this->createNotification($user, $position, 'profit', $gainLoss, $gainLossPercent, $currentPrice);
                }
            }

            // Vérifier si on doit créer une notification de perte
            if ($gainLoss <= $lossThreshold || $gainLossPercent <= -$percentThreshold) {
                $shouldNotify = false;
                
                if (!$lastNotification) {
                    // Première notification pour cette crypto si le seuil est dépassé
                    $shouldNotify = true;
                } else {
                    // Vérifier si on a déjà notifié récemment (dans les 15 dernières minutes)
                    $lastNotificationTime = $lastNotification->created_at;
                    $timeSinceLastNotification = now()->diffInMinutes($lastNotificationTime);
                    
                    // Si la dernière notification date de plus de 15 minutes
                    if ($timeSinceLastNotification >= 15) {
                        // Vérifier si c'est toujours une perte (même si pas de changement significatif)
                        $lastGainLoss = (float) ($lastNotification->gain_loss ?? 0);
                        $lastType = $lastNotification->type;
                        
                        // Notifier si :
                        // 1. La dernière notification était un profit et maintenant c'est une perte
                        // 2. OU si la perte a augmenté de 5€ ou plus
                        // 3. OU si le pourcentage a diminué de 1% ou plus
                        if ($lastType === 'profit' || 
                            ($lastGainLoss - $gainLoss) >= 5.0 ||
                            abs($gainLossPercent - (float)($lastNotification->gain_loss_percent ?? 0)) >= 1.0) {
                            $shouldNotify = true;
                        }
                    }
                }
                
                if ($shouldNotify) {
                    $this->createNotification($user, $position, 'loss', $gainLoss, $gainLossPercent, $currentPrice);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in checkPositionNotifications: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());
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
        float $gainLossPercent,
        ?float $currentPrice = null
    ): void {
        $cryptoSymbol = $position->crypto->symbol ?? 'Unknown';
        $cryptoName = $position->crypto->name ?? 'Unknown';
        $quantity = (float) ($position->quantity ?? 0);
        
        $isProfit = $type === 'profit';
        $sign = $isProfit ? '+' : '';
        $emoji = $isProfit ? '📈' : '📉';
        
        // Récupérer le prix précédent depuis la dernière notification si disponible
        $lastNotification = Notification::where('user_id', $user->id)
            ->where('crypto_currency_id', $position->crypto_currency_id)
            ->whereIn('type', ['profit', 'loss'])
            ->orderBy('created_at', 'desc')
            ->first();
        
        $previousPrice = $lastNotification ? $lastNotification->current_price : null;
        $finalCurrentPrice = $currentPrice ?? ($position->current_price ?? 0);
        
        $title = $isProfit 
            ? "{$emoji} Profit on {$cryptoSymbol}" 
            : "{$emoji} Loss on {$cryptoSymbol}";
        
        // Message plus détaillé avec quantité et prix
        $message = $isProfit
            ? "Your position in {$cryptoName} ({$cryptoSymbol}) has generated a profit of {$sign}€" . number_format(abs($gainLoss), 2) . " ({$sign}" . number_format(abs($gainLossPercent), 2) . "%). Quantity: " . number_format($quantity, 8) . " {$cryptoSymbol}"
            : "Your position in {$cryptoName} ({$cryptoSymbol}) has incurred a loss of {$sign}€" . number_format(abs($gainLoss), 2) . " ({$sign}" . number_format(abs($gainLossPercent), 2) . "%). Quantity: " . number_format($quantity, 8) . " {$cryptoSymbol}";

        $notification = Notification::create([
            'user_id' => $user->id,
            'portfolio_id' => $position->id,
            'crypto_currency_id' => $position->crypto_currency_id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'crypto_symbol' => $cryptoSymbol,
            'gain_loss' => round($gainLoss, 8),
            'gain_loss_percent' => round($gainLossPercent, 2),
            'current_price' => round($finalCurrentPrice, 8),
            'previous_price' => $previousPrice ? round($previousPrice, 8) : null,
            'is_read' => false,
        ]);

        $notification->load(['crypto', 'portfolio']);
        $this->notificationCacheService->store($user->id, $this->normalizeNotification($notification));
        
        // Supprimer les anciennes notifications si on dépasse le maximum (garder les 50 plus récentes)
        $this->cleanupOldNotifications($user->id, 50);
    }

    public function createTransactionNotification(
        User $user,
        string $type,
        string $symbol,
        float $quantity,
        float $euroAmount,
        float $price
    ): void {
        $isBuy = $type === 'buy';
        $title = $isBuy ? '✅ Achat confirmé' : '✅ Vente confirmée';
        $message = $isBuy
            ? "Achat de {$quantity} {$symbol} pour €" . number_format($euroAmount, 2)
            : "Vente de {$quantity} {$symbol} pour €" . number_format($euroAmount, 2);

        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'transaction',
            'title' => $title,
            'message' => $message,
            'crypto_symbol' => $symbol,
            'current_price' => round($price, 8),
            'is_read' => false,
        ]);

        $this->notificationCacheService->store($user->id, $this->normalizeNotification($notification));
    }
    
    /**
     * Supprime les anciennes notifications pour ne garder que les N plus récentes
     */
    private function cleanupOldNotifications(int $userId, int $keepCount = 50): void
    {
        try {
            // Compter le total de notifications
            $totalCount = Notification::where('user_id', $userId)->count();
            
            // Si on dépasse le maximum, supprimer les plus anciennes
            if ($totalCount > $keepCount) {
                $toDelete = $totalCount - $keepCount;
                
                // Récupérer les IDs des notifications les plus anciennes (lues en priorité, puis non lues)
                $oldNotifications = Notification::where('user_id', $userId)
                    ->orderBy('is_read', 'asc') // Les lues d'abord
                    ->orderBy('created_at', 'asc') // Puis les plus anciennes
                    ->limit($toDelete)
                    ->pluck('id');
                
                // Supprimer les anciennes notifications
                Notification::whereIn('id', $oldNotifications)->delete();
            }
        } catch (\Exception $e) {
            Log::error('Error cleaning up old notifications: ' . $e->getMessage());
        }
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

    private function normalizeNotification(Notification $notification): array
    {
        $notification->refresh();
        $cryptoSymbol = $notification->crypto_symbol;
        if (!$cryptoSymbol && $notification->crypto) {
            $cryptoSymbol = $notification->crypto->symbol;
        }

        return [
            'id' => $notification->id,
            'user_id' => $notification->user_id,
            'portfolio_id' => $notification->portfolio_id,
            'crypto_currency_id' => $notification->crypto_currency_id,
            'type' => $notification->type,
            'title' => $notification->title ?? 'Notification',
            'message' => $notification->message ?? '',
            'crypto_symbol' => $cryptoSymbol,
            'gain_loss' => $notification->gain_loss !== null ? (float) $notification->gain_loss : null,
            'gain_loss_percent' => $notification->gain_loss_percent !== null ? (float) $notification->gain_loss_percent : null,
            'current_price' => $notification->current_price !== null ? (float) $notification->current_price : null,
            'previous_price' => $notification->previous_price !== null ? (float) $notification->previous_price : null,
            'is_read' => (bool) $notification->is_read,
            'read_at' => $notification->read_at ? $notification->read_at->toISOString() : null,
            'level' => $notification->level !== null ? (int) $notification->level : null,
            'level_name' => $notification->level_name ?? null,
            'created_at' => $notification->created_at->toISOString(),
            'updated_at' => $notification->updated_at->toISOString(),
            'crypto' => $notification->crypto ? [
                'id' => $notification->crypto->id,
                'name' => $notification->crypto->name,
                'symbol' => $notification->crypto->symbol,
            ] : null,
        ];
    }
}

