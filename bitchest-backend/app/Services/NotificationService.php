<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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
                        (int) $result['total_trades'],
                        $result['xp_for_next_level'],
                        $result['trades_until_next']
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification de level up: ' . $e->getMessage());
        }
    }

    /**
     * Initialize level_up rows for one user: backfill total_trades on existing rows,
     * create missing tier notifications for levels 2..current (thresholds 5 / 10 / 20 trades).
     *
     * @return array{backfilled: int, created: int}
     */
    public function initializeLevelUpNotificationsForUser(User $user): array
    {
        $backfilled = $this->backfillLevelUpTotalTradesForUser($user->id);

        $this->levelService->updateUserLevel($user);
        $user->refresh();

        $currentLevel = (int) ($user->level ?? 1);
        $created = 0;

        for ($level = 2; $level <= min($currentLevel, LevelService::MAX_LEVEL); $level++) {
            $exists = Notification::where('user_id', $user->id)
                ->where('type', 'level_up')
                ->where('level', $level)
                ->exists();

            if ($exists) {
                continue;
            }

            $milestoneTrades = LevelService::TRADE_THRESHOLDS[$level];
            $tradesForNext = $this->levelService->getTradesRequiredForNextLevel($level);
            $tradesUntilNext = $tradesForNext !== null
                ? max(0, $tradesForNext - $milestoneTrades)
                : null;

            $this->createLevelUpNotification(
                $user,
                $level,
                $this->levelService->getLevelName($level),
                $milestoneTrades,
                $tradesForNext,
                $tradesUntilNext
            );
            $created++;
        }

        return ['backfilled' => $backfilled, 'created' => $created];
    }

    /**
     * Same as initializeLevelUpNotificationsForUser for every active client.
     *
     * @return array{users: int, backfilled: int, created: int}
     */
    public function initializeLevelUpNotificationsForAllClients(): array
    {
        $users = User::where('role', 'client')
            ->where('status', 'active')
            ->get();

        $totalBackfilled = 0;
        $totalCreated = 0;

        foreach ($users as $user) {
            try {
                $r = $this->initializeLevelUpNotificationsForUser($user);
                $totalBackfilled += $r['backfilled'];
                $totalCreated += $r['created'];
            } catch (\Exception $e) {
                Log::error('init level_up notifications: ' . $e->getMessage(), ['user_id' => $user->id]);
            }
        }

        return [
            'users' => $users->count(),
            'backfilled' => $totalBackfilled,
            'created' => $totalCreated,
        ];
    }

    /**
     * Sets total_trades on existing level_up notifications (tier threshold value).
     */
    private function backfillLevelUpTotalTradesForUser(int $userId): int
    {
        if (! Schema::hasColumn('notifications', 'total_trades')) {
            return 0;
        }

        $updated = 0;
        Notification::query()
            ->where('user_id', $userId)
            ->where('type', 'level_up')
            ->whereNull('total_trades')
            ->whereNotNull('level')
            ->whereBetween('level', [2, LevelService::MAX_LEVEL])
            ->chunkById(100, function ($notifications) use (&$updated) {
                foreach ($notifications as $n) {
                    $level = (int) $n->level;
                    $n->total_trades = LevelService::TRADE_THRESHOLDS[$level];
                    $n->save();
                    $updated++;
                }
            });

        return $updated;
    }
    
    /**
     * Persists a level-up notification (user-facing copy in English).
     */
    private function createLevelUpNotification(
        User $user,
        int $level,
        string $levelName,
        int $totalTrades,
        ?int $tradesForNextLevel,
        ?int $tradesUntilNext
    ): void {
        $message = "Level {$level} — {$levelName}! You have completed {$totalTrades} trade(s) in total (buys and sells).";
        if ($tradesForNextLevel !== null) {
            $message .= " Next level at {$tradesForNextLevel} total trades";
            if ($tradesUntilNext !== null && $tradesUntilNext > 0) {
                $message .= " ({$tradesUntilNext} more to go).";
            } else {
                $message .= '.';
            }
        } else {
            $message .= ' You have reached the maximum level.';
        }

        $payload = [
            'user_id' => $user->id,
            'type' => 'level_up',
            'title' => '🎉 Level up!',
            'message' => $message,
            'level' => $level,
            'level_name' => $levelName,
            'is_read' => false,
        ];
        if (Schema::hasColumn('notifications', 'total_trades')) {
            $payload['total_trades'] = $totalTrades;
        }

        $notification = Notification::create($payload);

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
            
            $quantity = (float) ($position->quantity ?? 0);
            
            if ($quantity <= 0) {
                return;
            }

            $cryptoId = (int) ($position->crypto_currency_id ?? 0);
            if ($cryptoId <= 0) {
                return;
            }

            // Se baser sur la variation réelle du prix (2 derniers points)
            $priceRecords = \App\Models\CryptoPriceRecord::where('crypto_currency_id', $cryptoId)
                ->orderBy('recorded_at', 'desc')
                ->limit(2)
                ->get();

            if ($priceRecords->count() < 2) {
                return;
            }

            $currentPrice = (float) ($priceRecords[0]->price ?? 0);
            $previousPrice = (float) ($priceRecords[1]->price ?? 0);

            if ($currentPrice <= 0 || $previousPrice <= 0) {
                return;
            }

            if (abs($currentPrice - $previousPrice) < 0.00000001) {
                return; // Pas de variation exploitable
            }

            $priceChangePercent = ($previousPrice > 0)
                ? (($currentPrice - $previousPrice) / $previousPrice) * 100
                : 0.0;

            // Variation en euros pour la quantité détenue (cohérent avec l'affichage gain_loss)
            $gainLoss = ($currentPrice - $previousPrice) * $quantity;
            $gainLossPercent = $priceChangePercent;

            // Seuil minimum pour éviter le bruit (en € OU en %)
            if (abs($gainLoss) < self::NOTIFICATION_THRESHOLD_PROFIT && abs($gainLossPercent) < self::NOTIFICATION_THRESHOLD_PERCENT) {
                return;
            }
            
            $type = $gainLoss >= 0 ? 'profit' : 'loss';

            // Cooldown (inchangé) : dernière notification profit/loss pour cette crypto
            $lastNotification = $this->getLastNotification($user->id, $cryptoId);
            if ($lastNotification && now()->diffInMinutes($lastNotification->created_at) < self::NOTIFICATION_COOLDOWN) {
                return;
            }

            // Anti-doublons : même user + crypto + type + mêmes prix
            $lastNotificationForType = $this->getLastNotification($user->id, $cryptoId, $type);
            if ($lastNotificationForType) {
                $lastCurrent = (float) ($lastNotificationForType->current_price ?? 0);
                $lastPrevious = (float) ($lastNotificationForType->previous_price ?? 0);

                if (
                    abs($lastCurrent - $currentPrice) < 0.00000001 &&
                    abs($lastPrevious - $previousPrice) < 0.00000001
                ) {
                    return;
                }
            }

            $this->createNotification(
                $user,
                $position,
                $type,
                $gainLoss,
                $gainLossPercent,
                $currentPrice,
                $previousPrice
            );
        } catch (\Exception $e) {
            Log::error('Erreur dans checkPositionNotifications: ' . $e->getMessage());
        }
    }
    
    /**
     * Récupère la dernière notification pour une crypto
     */
    private function getLastNotification(int $userId, int $cryptoId, ?string $type = null): ?Notification
    {
        $query = Notification::where('user_id', $userId)
            ->where('crypto_currency_id', $cryptoId)
            ->whereIn('type', ['profit', 'loss']);

        if ($type !== null) {
            $query->where('type', $type);
        }

        return $query->orderBy('created_at', 'desc')->first();
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
        float $currentPrice,
        float $previousPrice
    ): void {
        $cryptoSymbol = $position->crypto->symbol ?? 'Unknown';
        $cryptoName = $position->crypto->name ?? 'Unknown';
        $quantity = (float) ($position->quantity ?? 0);

        $portfolioId = \App\Models\Portfolio::where('user_id', $user->id)
            ->where('crypto_currency_id', (int) ($position->crypto_currency_id ?? 0))
            ->value('id');

        if (!$portfolioId) {
            Log::warning('Portfolio introuvable pour la notification', [
                'user_id' => $user->id,
                'crypto_currency_id' => $position->crypto_currency_id ?? null,
            ]);
            return;
        }
        
        $isProfit = $type === 'profit';
        $sign = $isProfit ? '+' : '';
        $emoji = $isProfit ? '📈' : '📉';
        
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
            'portfolio_id' => $portfolioId,
            'crypto_currency_id' => $position->crypto_currency_id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'crypto_symbol' => $cryptoSymbol,
            'gain_loss' => round($gainLoss, 8),
            'gain_loss_percent' => round($gainLossPercent, 2),
            'current_price' => round($currentPrice, 8),
            'previous_price' => round($previousPrice, 8),
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
            'title' => $isBuy ? '✅ Purchase confirmed' : '✅ Sale confirmed',
            'message' => $isBuy
                ? 'Bought ' . number_format($quantity, 8) . " {$symbol} for €" . number_format($euroAmount, 2)
                : 'Sold ' . number_format($quantity, 8) . " {$symbol} for €" . number_format($euroAmount, 2),
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
            'total_trades' => $notification->total_trades !== null ? (int) $notification->total_trades : null,
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
