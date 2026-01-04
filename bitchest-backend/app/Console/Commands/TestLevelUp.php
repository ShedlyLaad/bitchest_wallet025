<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\LevelService;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class TestLevelUp extends Command
{
    protected $signature = 'test:level-up {email? : Email de l\'utilisateur à tester}';
    protected $description = 'Teste la création d\'une notification de level up pour un utilisateur';

    public function handle(LevelService $levelService, NotificationService $notificationService)
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
        
        $this->info("Test de level up pour: {$user->email}");
        $this->info("Niveau actuel: " . ($user->level ?? 1));
        $this->info("XP actuel: " . ($user->experience_points ?? 0));
        
        // Forcer une montée de niveau en ajoutant de l'XP directement dans la base
        $currentXp = (int) ($user->experience_points ?? 0);
        $currentLevel = (int) ($user->level ?? 1);
        
        // Calculer l'XP nécessaire pour le niveau suivant
        // Formule: level = floor(sqrt(xp / 100)) + 1
        // Pour niveau 3: xp >= 400
        $xpForNextLevel = (int) (pow($currentLevel, 2) * 100);
        $newXp = $xpForNextLevel + 50; // Ajouter assez d'XP pour monter de niveau
        
        $this->info("XP actuel: {$currentXp}");
        $this->info("Niveau actuel: {$currentLevel}");
        $this->info("XP nécessaire pour niveau suivant: {$xpForNextLevel}");
        $this->info("Nouveau XP à définir: {$newXp}");
        
        // Mettre à jour l'XP manuellement pour forcer un level up
        $user->experience_points = $currentXp; // Garder l'ancien XP pour la comparaison
        $user->level = $currentLevel;
        $user->save();
        
        // Maintenant mettre à jour avec le nouveau XP pour forcer le level up
        $user->experience_points = $newXp;
        $user->save();
        
        // Maintenant tester la détection de level up
        $this->info("\nVérification du level up...");
        $notificationService->checkLevelUp($user);
        
        $user->refresh();
        $this->info("Niveau après vérification: " . $user->level);
        $this->info("XP après vérification: " . $user->experience_points);
        
        // Vérifier si une notification a été créée
        $notification = \App\Models\Notification::where('user_id', $user->id)
            ->where('type', 'level_up')
            ->latest()
            ->first();
        
        if ($notification) {
            $this->info("\n✅ Notification créée:");
            $this->info("  - Titre: {$notification->title}");
            $this->info("  - Message: {$notification->message}");
            $this->info("  - Niveau: {$notification->level}");
            $this->info("  - Nom du niveau: {$notification->level_name}");
        } else {
            $this->warn("\n⚠️  Aucune notification créée");
        }
        
        return 0;
    }
}

