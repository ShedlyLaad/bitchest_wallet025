<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Console\Command;

class CreateTestLevelUpNotification extends Command
{
    protected $signature = 'test:create-level-up-notification {email? : Email de l\'utilisateur}';
    protected $description = 'Crée une notification de test de level up pour vérifier l\'affichage';

    public function handle()
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
        
        $currentLevel = (int) ($user->level ?? 1);
        $newLevel = $currentLevel + 1;
        
        $levelNames = [
            1 => 'Beginner Trader',
            2 => 'Beginner Trader',
            3 => 'Intermediate Trader',
            10 => 'Intermediate Trader',
            20 => 'Skilled Trader',
            30 => 'Experienced Trader',
            45 => 'Professional Trader',
            60 => 'Advanced Trader',
            75 => 'Expert Trader',
            90 => 'Master Trader',
        ];
        
        $levelName = 'Beginner Trader';
        foreach ($levelNames as $threshold => $name) {
            if ($newLevel >= $threshold) {
                $levelName = $name;
            }
        }
        
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'level_up',
            'title' => '🎉 Level Up!',
            'message' => "Congratulations! You have reached level {$newLevel} - {$levelName}! Keep trading to level up even higher.",
            'level' => $newLevel,
            'level_name' => $levelName,
            'is_read' => false,
        ]);
        
        $this->info("✅ Notification de level up créée pour {$user->email}");
        $this->info("  - ID: {$notification->id}");
        $this->info("  - Niveau: {$newLevel}");
        $this->info("  - Nom: {$levelName}");
        $this->info("  - Titre: {$notification->title}");
        
        return 0;
    }
}

