<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class CheckPortfolioNotifications extends Command
{
    protected $signature = 'notifications:check-portfolio';
    protected $description = 'Vérifie les portfolios et génère des notifications de profit/loss automatiquement';

    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    public function handle()
    {
        $this->info('Checking portfolios and generating notifications...');
        
        $users = User::where('role', 'client')
            ->where('status', 'active')
            ->get();
        
        $totalNotifications = 0;
        
        foreach ($users as $user) {
            try {
                $this->notificationService->checkAndCreatePortfolioNotifications($user);
                $totalNotifications++;
            } catch (\Exception $e) {
                $this->error("Error for user {$user->email}: " . $e->getMessage());
            }
        }
        
        $this->info("Done. Checked {$users->count()} user(s).");
        
        return 0;
    }
}
