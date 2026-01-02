<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\NotificationService;

class CheckPortfolioNotifications extends Command
{
    protected $signature = 'notifications:check-portfolio';
    protected $description = 'Check and create notifications for portfolio profit/loss changes';

    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    public function handle()
    {
        $this->info('Checking portfolio notifications...');

        $users = User::where('role', 'client')
            ->where('status', 'active')
            ->get();

        $count = 0;
        foreach ($users as $user) {
            try {
                $this->notificationService->checkAndCreatePortfolioNotifications($user);
                $count++;
            } catch (\Exception $e) {
                $this->error("Error checking notifications for user {$user->id}: " . $e->getMessage());
            }
        }

        $this->info("Checked notifications for {$count} users.");
        return 0;
    }
}

