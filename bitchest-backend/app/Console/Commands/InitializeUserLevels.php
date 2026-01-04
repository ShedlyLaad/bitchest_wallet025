<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\LevelService;
use Illuminate\Console\Command;

class InitializeUserLevels extends Command
{
    protected $signature = 'users:initialize-levels';
    protected $description = 'Initialize levels and experience points for all existing users';

    public function handle(LevelService $levelService)
    {
        $this->info('🔄 Initializing user levels...');
        
        $users = User::where('role', 'client')->get();
        $count = 0;
        
        foreach ($users as $user) {
            $result = $levelService->updateUserLevel($user);
            $count++;
            $this->info("✅ User {$user->email}: Level {$result['new_level']} ({$result['new_xp']} XP)");
        }
        
        $this->info("✨ Initialized levels for {$count} users!");
        
        return 0;
    }
}
