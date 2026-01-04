<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Notification;
use App\Services\NotificationService;
use App\Services\PortfolioService;
use Illuminate\Console\Command;

class TestNotificationSystem extends Command
{
    protected $signature = 'test:notifications {email?}';
    protected $description = 'Teste le système de notifications';

    public function handle(NotificationService $notificationService, PortfolioService $portfolioService)
    {
        $email = $this->argument('email');
        
        if ($email) {
            $user = User::where('email', $email)->where('role', 'client')->first();
            if (!$user) {
                $this->error("Utilisateur non trouvé: {$email}");
                return 1;
            }
        } else {
            $user = User::where('role', 'client')->first();
            if (!$user) {
                $this->error("Aucun utilisateur client trouvé");
                return 1;
            }
        }
        
        $this->info("Test des notifications pour: {$user->email}");
        
        // Afficher les notifications existantes
        $existingCount = Notification::where('user_id', $user->id)->count();
        $this->info("Notifications existantes: {$existingCount}");
        
        // Afficher le portfolio
        $portfolio = $portfolioService->getUserPortfolio($user);
        $this->info("Positions dans le portfolio: " . $portfolio->count());
        
        foreach ($portfolio as $pos) {
            $this->info("  - {$pos->crypto->symbol}: Qty={$pos->quantity}, Gain/Loss={$pos->gain_loss}€ ({$pos->gain_loss_percent}%)");
        }
        
        // Tester la création de notifications
        $this->info("\nVérification des notifications...");
        $notificationService->checkAndCreatePortfolioNotifications($user);
        
        // Afficher les nouvelles notifications
        $newCount = Notification::where('user_id', $user->id)->count();
        $createdCount = $newCount - $existingCount;
        $this->info("Nouvelles notifications créées: {$createdCount}");
        
        // Afficher les dernières notifications
        $recentNotifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        $this->info("\nDernières notifications:");
        foreach ($recentNotifications as $notif) {
            $this->info("  - [{$notif->type}] {$notif->title} - {$notif->message}");
        }
        
        return 0;
    }
}

