<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\LevelService;
use Illuminate\Console\Command;

class InitializeUserLevels extends Command
{
    protected $signature = 'users:initialize-levels';
    protected $description = 'Initialize levels and trade counts for all existing client users';

    public function handle(LevelService $levelService)
    {
        $this->info('Initializing user levels from trade count...');
        
        $users = User::where('role', 'client')->get();
        $count = 0;
        
        foreach ($users as $user) {
            $result = $levelService->updateUserLevel($user);
            $count++;
            $trades = $result['total_trades'];
            $this->info("✅ {$user->email}: level {$result['new_level']} — {$trades} trade(s)");
        }
        
        $this->info("Done. Updated {$count} user(s).");
        
        return 0;
    }
}
