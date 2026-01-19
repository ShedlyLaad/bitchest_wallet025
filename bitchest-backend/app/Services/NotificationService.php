<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Service de gestion des notifications
 * 
 * Responsabilités :
 * - Création de notifications (profit, loss, level_up, transaction)
 * - Vérification des portfolios pour générer des notifications
 * - Gestion de l'état de lecture
 * - Optimisation via cache Redis
 */
class NotificationService
{
    private const NOTIFICATION_THRESHOLD_PROFIT = 0.01; // €0.01 minimum
    private const NOTIFICATION_THRESHOLD_LOSS = -0.01; // €0.01 minimum
    private const NOTIFICATION_THRESHOLD_PERCENT = 0.1; // 0.1% minimum
    private const NOTIFICATION_COOLDOWN = 15; // 15 minutes entre notifications similaires
    private const MAX_NOTIFICATIONS = 50; // Nombre maximum de notifications à garder
    
    public function __construct(
        private PortfolioService $portfolioService,
        private LevelService $levelService,
        private NotificationCacheService $notificationCacheService
    ) {}

    /**
     * Vérifie et crée des notifications pour le portfolio d'un utilisateur
     * 
     * @param User $user Utilisateur
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
            Log::error('Erreur lors de la vérification des notifications: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Vérifie si l'utilisateur a monté de niveau
     */
    public function checkLevelUp(User $user): void
    {
        try {
            $user->refresh();
            
            $oldLevel = (int) ($user->level ?? 1);
            
            // Calculer les nouveaux points et niveau
            $result = $this->levelService->updateUserLevel($user);
            $user->refresh();
            
            $newLevel = $result['new_level'];
            
            // Vérifier si le niveau a vraiment augmenté
            if ($result['level_up'] && $newLevel > $oldLevel) {
                // Vérifier si on a déjà notifié ce niveau récemment
                $recentNotification = Notification::where('user_id', $user->id)
                    ->where('type', 'level_up')
                    ->where('level', $newLevel)
                    ->where('created_at', '>=', now()->subMinutes(5))
                    ->first();
                
                if (!$recentNotification) {
                    $this->createLevelUpNotification(
                        $user,
                        $newLevel,
                        $this->levelService->getLevelName($newLevel),
                        $result['new_xp'],
                        $result['xp_for_next_level']
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification de level up: ' . $e->getMessage());
        }
    }
    
    /**
     * Crée une notification de montée de niveau
     */
    private function createLevelUpNotification(
        User $user,
        int $level,
        string $levelName,
        int $currentXp,
        int $xpForNext
    ): void {
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'level_up',
            'title' => "🎉 Level Up!",
            'message' => "Congratulations! You have reached level {$level} - {$levelName}! Keep trading to level up even higher.",
            'level' => $level,
            'level_name' => $levelName,
            'is_read' => false,
        ]);

        $this->notificationCacheService->store($user->id, $this->normalizeNotification($notification));
        $this->cleanupOldNotifications($user->id);
    }

    /**
     * Vérifie et crée des notifications pour une position spécifique
     */
    private function checkPositionNotifications(User $user, $position): void
    {
        try {
            // Validation des données
            if (!$position || !isset($position->crypto) || !$position->crypto) {
                return;
            }
            
            $gainLoss = (float) ($position->gain_loss ?? 0);
            $gainLossPercent = (float) ($position->gain_loss_percent ?? 0);
            $quantity = (float) ($position->quantity ?? 0);
            
            if ($quantity <= 0) {
                return;
            }
            
            // Récupérer la dernière notification pour cette crypto
            $lastNotification = $this->getLastNotification($user->id, $position->crypto_currency_id);
            
            // Vérifier les notifications de profit
            if ($gainLoss >= self::NOTIFICATION_THRESHOLD_PROFIT || 
                $gainLossPercent >= self::NOTIFICATION_THRESHOLD_PERCENT) {
                if ($this->shouldNotifyProfit($lastNotification, $gainLoss, $gainLossPercent)) {
                    $this->createNotification(
                        $user,
                        $position,
                        'profit',
                        $gainLoss,
                        $gainLossPercent,
                        (float) ($position->current_price ?? 0)
                    );
                }
            }
            
            // Vérifier les notifications de perte
            if ($gainLoss <= self::NOTIFICATION_THRESHOLD_LOSS || 
                $gainLossPercent <= -self::NOTIFICATION_THRESHOLD_PERCENT) {
                if ($this->shouldNotifyLoss($lastNotification, $gainLoss, $gainLossPercent)) {
                    $this->createNotification(
                        $user,
                        $position,
                        'loss',
                        $gainLoss,
                        $gainLossPercent,
                        (float) ($position->current_price ?? 0)
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur dans checkPositionNotifications: ' . $e->getMessage());
        }
    }
    
    /**
     * Récupère la dernière notification pour une crypto
     */
    private function getLastNotification(int $userId, int $cryptoId): ?Notification
    {
        return Notification::where('user_id', $userId)
            ->where('crypto_currency_id', $cryptoId)
            ->whereIn('type', ['profit', 'loss'])
            ->orderBy('created_at', 'desc')
            ->first();
    }
    
    /**
     * Détermine si on doit notifier un profit
     */
    private function shouldNotifyProfit(?Notification $lastNotification, float $gainLoss, float $gainLossPercent): bool
    {
        if (!$lastNotification) {
            return true; // Première notification
        }
        
        // Vérifier le cooldown
        if (now()->diffInMinutes($lastNotification->created_at) < self::NOTIFICATION_COOLDOWN) {
            return false;
        }
        
        $lastGainLoss = (float) ($lastNotification->gain_loss ?? 0);
        $lastType = $lastNotification->type;
        
        // Notifier si :
        // 1. Dernière notification était une perte
        // 2. OU le gain a augmenté de 5€ ou plus
        // 3. OU le pourcentage a augmenté de 1% ou plus
        return $lastType === 'loss' ||
            ($gainLoss - $lastGainLoss) >= 5.0 ||
            abs($gainLossPercent - (float)($lastNotification->gain_loss_percent ?? 0)) >= 1.0;
    }
    
    /**
     * Détermine si on doit notifier une perte
     */
    private function shouldNotifyLoss(?Notification $lastNotification, float $gainLoss, float $gainLossPercent): bool
    {
        if (!$lastNotification) {
            return true; // Première notification
        }
        
        // Vérifier le cooldown
        if (now()->diffInMinutes($lastNotification->created_at) < self::NOTIFICATION_COOLDOWN) {
            return false;
        }
        
        $lastGainLoss = (float) ($lastNotification->gain_loss ?? 0);
        $lastType = $lastNotification->type;
        
        // Notifier si :
        // 1. Dernière notification était un profit
        // 2. OU la perte a augmenté de 5€ ou plus
        // 3. OU le pourcentage a diminué de 1% ou plus
        return $lastType === 'profit' ||
            ($lastGainLoss - $gainLoss) >= 5.0 ||
            abs($gainLossPercent - (float)($lastNotification->gain_loss_percent ?? 0)) >= 1.0;
    }
    
    /**
     * Crée une notification de profit ou de perte
     */
    private function createNotification(
        User $user,
        $position,
        string $type,
        float $gainLoss,
        float $gainLossPercent,
        float $currentPrice
    ): void {
        $cryptoSymbol = $position->crypto->symbol ?? 'Unknown';
        $cryptoName = $position->crypto->name ?? 'Unknown';
        $quantity = (float) ($position->quantity ?? 0);
        
        $isProfit = $type === 'profit';
        $sign = $isProfit ? '+' : '';
        $emoji = $isProfit ? '📈' : '📉';
        
        // Récupérer le prix précédent
        $lastNotification = $this->getLastNotification($user->id, $position->crypto_currency_id);
        $previousPrice = $lastNotification ? $lastNotification->current_price : null;
        
        $title = $isProfit
            ? "{$emoji} Profit on {$cryptoSymbol}"
            : "{$emoji} Loss on {$cryptoSymbol}";
        
        $message = $isProfit
            ? "Your position in {$cryptoName} ({$cryptoSymbol}) has generated a profit of {$sign}€" . 
              number_format(abs($gainLoss), 2) . " ({$sign}" . number_format(abs($gainLossPercent), 2) . 
              "%). Quantity: " . number_format($quantity, 8) . " {$cryptoSymbol}"
            : "Your position in {$cryptoName} ({$cryptoSymbol}) has incurred a loss of {$sign}€" . 
              number_format(abs($gainLoss), 2) . " ({$sign}" . number_format(abs($gainLossPercent), 2) . 
              "%). Quantity: " . number_format($quantity, 8) . " {$cryptoSymbol}";

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
            'current_price' => round($currentPrice, 8),
            'previous_price' => $previousPrice ? round($previousPrice, 8) : null,
            'is_read' => false,
        ]);

        $notification->load(['crypto', 'portfolio']);
        $this->notificationCacheService->store($user->id, $this->normalizeNotification($notification));
        $this->cleanupOldNotifications($user->id);
    }

    /**
     * Crée une notification de transaction
     */
    public function createTransactionNotification(
        User $user,
        string $type,
        string $symbol,
        float $quantity,
        float $euroAmount,
        float $price
    ): void {
        $isBuy = $type === 'buy';
        
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'portfolio_update',
            'title' => $isBuy ? '✅ Achat confirmé' : '✅ Vente confirmée',
            'message' => $isBuy
                ? "Achat de " . number_format($quantity, 8) . " {$symbol} pour €" . number_format($euroAmount, 2)
                : "Vente de " . number_format($quantity, 8) . " {$symbol} pour €" . number_format($euroAmount, 2),
            'crypto_symbol' => $symbol,
            'current_price' => round($price, 8),
            'is_read' => false,
        ]);

        $this->notificationCacheService->store($user->id, $this->normalizeNotification($notification));
    }
    
    /**
     * Supprime les anciennes notifications (garde les N plus récentes)
     */
    private function cleanupOldNotifications(int $userId, int $keepCount = self::MAX_NOTIFICATIONS): void
    {
        try {
            $totalCount = Notification::where('user_id', $userId)->count();
            
            if ($totalCount > $keepCount) {
                $toDelete = $totalCount - $keepCount;
                
                $oldNotifications = Notification::where('user_id', $userId)
                    ->orderBy('is_read', 'asc') // Les lues d'abord
                    ->orderBy('created_at', 'asc') // Puis les plus anciennes
                    ->limit($toDelete)
                    ->pluck('id');
                
                Notification::whereIn('id', $oldNotifications)->delete();
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors du nettoyage des notifications: ' . $e->getMessage());
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

    /**
     * Normalise une notification pour le cache
     */
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
