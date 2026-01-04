<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Portfolio;

class LevelService
{
    /**
     * Calcule les points d'expérience basés sur l'activité de l'utilisateur
     * 
     * @param User $user
     * @return int Points d'expérience totaux
     */
    public function calculateExperiencePoints(User $user): int
    {
        $xp = 0;
        
        // XP basé sur le volume de transactions (1 XP par 10€ de volume)
        $totalVolume = Transaction::whereHas('portfolio', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->sum('euro_amount');
        $xp += (int) ($totalVolume / 10);
        
        // XP basé sur le nombre de transactions (10 XP par transaction)
        $transactionCount = Transaction::whereHas('portfolio', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();
        $xp += $transactionCount * 10;
        
        // XP basé sur la valeur du portfolio (1 XP par 100€ de valeur)
        try {
            $portfolioService = app(PortfolioService::class);
            $portfolio = $portfolioService->getUserPortfolio($user);
            $totalPortfolioValue = $portfolio->sum('current_value') ?? 0;
            $xp += (int) ($totalPortfolioValue / 100);
            
            // XP basé sur les gains totaux (2 XP par 10€ de gain)
            $totalGainLoss = $portfolio->sum('gain_loss') ?? 0;
            if ($totalGainLoss > 0) {
                $xp += (int) (($totalGainLoss / 10) * 2);
            }
        } catch (\Exception $e) {
            // En cas d'erreur, continuer sans ces XP
        }
        
        return max(0, $xp);
    }
    
    /**
     * Calcule le niveau basé sur les points d'expérience
     * Formule : level = floor(sqrt(xp / 100)) + 1
     * 
     * @param int $experiencePoints
     * @return int Niveau (1-100)
     */
    public function calculateLevel(int $experiencePoints): int
    {
        // Formule progressive : plus on monte, plus c'est difficile
        // Niveau 1: 0-99 XP
        // Niveau 2: 100-399 XP
        // Niveau 3: 400-899 XP
        // etc.
        $level = (int) floor(sqrt($experiencePoints / 100)) + 1;
        return min(100, max(1, $level)); // Limiter entre 1 et 100
    }
    
    /**
     * Calcule les XP nécessaires pour le prochain niveau
     * 
     * @param int $currentLevel
     * @return int XP nécessaire pour le niveau suivant
     */
    public function getXpForNextLevel(int $currentLevel): int
    {
        // XP nécessaire pour le niveau suivant
        // Formule: level = floor(sqrt(xp / 100)) + 1
        // Pour niveau N: xp >= (N-1)^2 * 100
        $nextLevel = $currentLevel + 1;
        return (int) (pow($nextLevel - 1, 2) * 100);
    }
    
    /**
     * Calcule les XP nécessaires pour le niveau actuel
     * 
     * @param int $currentLevel
     * @return int XP minimum pour le niveau actuel
     */
    public function getXpForCurrentLevel(int $currentLevel): int
    {
        // XP minimum pour le niveau actuel
        if ($currentLevel <= 1) return 0;
        return (int) (pow($currentLevel - 1, 2) * 100);
    }
    
    /**
     * Met à jour le niveau et l'expérience de l'utilisateur
     * Retourne true si le niveau a augmenté
     * 
     * @param User $user
     * @return array ['level_up' => bool, 'old_level' => int, 'new_level' => int, 'xp' => int]
     */
    public function updateUserLevel(User $user): array
    {
        // Recharger l'utilisateur pour avoir les valeurs à jour
        $user->refresh();
        
        $oldLevel = (int) ($user->level ?? 1);
        $oldXp = (int) ($user->experience_points ?? 0);
        
        // Calculer les nouveaux points d'expérience
        $newXp = $this->calculateExperiencePoints($user);
        
        // Calculer le nouveau niveau
        $newLevel = $this->calculateLevel($newXp);
        
        // Vérifier si le niveau a augmenté AVANT de mettre à jour
        $levelUp = $newLevel > $oldLevel;
        
        // Mettre à jour l'utilisateur seulement si les valeurs ont changé
        if ($newXp != $oldXp || $newLevel != $oldLevel) {
            $user->experience_points = $newXp;
            $user->level = $newLevel;
            $user->save();
        }
        
        return [
            'level_up' => $levelUp,
            'old_level' => $oldLevel,
            'new_level' => $newLevel,
            'old_xp' => $oldXp,
            'new_xp' => $newXp,
            'xp_for_next_level' => $this->getXpForNextLevel($newLevel),
        ];
    }
    
    /**
     * Obtient le nom du niveau (pour l'affichage)
     * 
     * @param int $level
     * @return string Nom du niveau
     */
    public function getLevelName(int $level): string
    {
        if ($level >= 90) return 'Master Trader';
        if ($level >= 75) return 'Expert Trader';
        if ($level >= 60) return 'Advanced Trader';
        if ($level >= 45) return 'Professional Trader';
        if ($level >= 30) return 'Experienced Trader';
        if ($level >= 20) return 'Skilled Trader';
        if ($level >= 10) return 'Intermediate Trader';
        return 'Beginner Trader';
    }
    
    /**
     * Obtient la couleur du niveau (pour l'affichage)
     * 
     * @param int $level
     * @return string Couleur (classe Tailwind)
     */
    public function getLevelColor(int $level): string
    {
        if ($level >= 90) return 'purple';
        if ($level >= 75) return 'blue';
        if ($level >= 60) return 'cyan';
        if ($level >= 45) return 'green';
        if ($level >= 30) return 'yellow';
        if ($level >= 20) return 'orange';
        if ($level >= 10) return 'amber';
        return 'gray';
    }
}

