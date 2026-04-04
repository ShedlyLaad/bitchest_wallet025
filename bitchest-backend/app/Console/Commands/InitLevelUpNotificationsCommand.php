<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class InitLevelUpNotificationsCommand extends Command
{
    protected $signature = 'notifications:init-level-up
                            {--user= : Client user ID (omit for all active clients)}';

    protected $description = 'Initialize level_up notifications: backfill total_trades and create missing tier notifications (5 / 10 / 20 trades)';

    public function handle(NotificationService $notificationService): int
    {
        $userId = $this->option('user');

        if ($userId !== null && $userId !== '') {
            $user = User::where('id', (int) $userId)->where('role', 'client')->first();
            if (! $user) {
                $this->error('No client user found for this ID.');

                return 1;
            }

            $r = $notificationService->initializeLevelUpNotificationsForUser($user);
            $this->info("User #{$user->id} ({$user->email})");
            $this->line("  → total_trades backfilled (existing rows): {$r['backfilled']}");
            $this->line("  → level_up notifications created: {$r['created']}");

            return 0;
        }

        $this->info('Initializing for all active clients...');
        $summary = $notificationService->initializeLevelUpNotificationsForAllClients();
        $this->info("Users processed: {$summary['users']}");
        $this->line("  → total_trades backfilled (existing rows): {$summary['backfilled']}");
        $this->line("  → level_up notifications created: {$summary['created']}");

        return 0;
    }
}
