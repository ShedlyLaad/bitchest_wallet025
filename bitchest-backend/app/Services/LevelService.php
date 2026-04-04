<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;

/**
 * User levels based on total completed trades (buys + sells).
 *
 * Rules: level 1 by default (fewer than 5 trades), level 2 at 5+, 3 at 10+, 4 at 20+ (max).
 * User experience_points column stores the live trade count for display.
 */
class LevelService
{
    public const MAX_LEVEL = 4;

    /** Level => minimum total trades (buys + sells) to reach that level */
    public const TRADE_THRESHOLDS = [
        1 => 0,
        2 => 5,
        3 => 10,
        4 => 20,
    ];

    /**
     * Total transaction count (buy + sell) for the user.
     */
    public function countUserTrades(User $user): int
    {
        return (int) Transaction::whereHas('portfolio', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();
    }

    /**
     * @deprecated Use countUserTrades().
     */
    public function calculateExperiencePoints(User $user): int
    {
        return $this->countUserTrades($user);
    }

    public function calculateLevelFromTradeCount(int $tradeCount): int
    {
        if ($tradeCount >= self::TRADE_THRESHOLDS[4]) {
            return 4;
        }
        if ($tradeCount >= self::TRADE_THRESHOLDS[3]) {
            return 3;
        }
        if ($tradeCount >= self::TRADE_THRESHOLDS[2]) {
            return 2;
        }

        return 1;
    }

    /**
     * @deprecated Use calculateLevelFromTradeCount()
     */
    public function calculateLevel(int $experiencePoints): int
    {
        return $this->calculateLevelFromTradeCount($experiencePoints);
    }

    /**
     * Minimum trades required to be at the given level.
     */
    public function getMinTradesForLevel(int $level): int
    {
        $level = max(1, min(self::MAX_LEVEL, $level));

        return self::TRADE_THRESHOLDS[$level];
    }

    /**
     * Total trades required for the next level, or null at max level.
     */
    public function getTradesRequiredForNextLevel(int $currentLevel): ?int
    {
        if ($currentLevel >= self::MAX_LEVEL) {
            return null;
        }

        return self::TRADE_THRESHOLDS[$currentLevel + 1];
    }

    /**
     * @deprecated Alias: in this model, "XP" maps to trade counts.
     */
    public function getXpForNextLevel(int $currentLevel): int
    {
        $next = $this->getTradesRequiredForNextLevel($currentLevel);

        return $next ?? self::TRADE_THRESHOLDS[self::MAX_LEVEL];
    }

    /**
     * @deprecated Alias: in this model, "XP" maps to trade counts.
     */
    public function getXpForCurrentLevel(int $currentLevel): int
    {
        return $this->getMinTradesForLevel($currentLevel);
    }

    /**
     * Updates user level and experience_points (trade count).
     *
     * @return array{
     *   level_up: bool,
     *   old_level: int,
     *   new_level: int,
     *   old_xp: int,
     *   new_xp: int,
     *   total_trades: int,
     *   xp_for_next_level: int|null,
     *   trades_until_next: int|null
     * }
     */
    public function updateUserLevel(User $user): array
    {
        $user->refresh();

        $oldLevel = (int) ($user->level ?? 1);
        $totalTrades = $this->countUserTrades($user);
        $newLevel = $this->calculateLevelFromTradeCount($totalTrades);

        $levelUp = $newLevel > $oldLevel;

        $tradesForNext = $this->getTradesRequiredForNextLevel($newLevel);
        $tradesUntilNext = $tradesForNext !== null ? max(0, $tradesForNext - $totalTrades) : null;

        $oldXp = (int) ($user->experience_points ?? 0);

        if ($totalTrades !== $oldXp || $newLevel !== $oldLevel) {
            $user->experience_points = $totalTrades;
            $user->level = $newLevel;
            $user->save();
        }

        return [
            'level_up' => $levelUp,
            'old_level' => $oldLevel,
            'new_level' => $newLevel,
            'old_xp' => $oldXp,
            'new_xp' => $totalTrades,
            'total_trades' => $totalTrades,
            'xp_for_next_level' => $tradesForNext,
            'trades_until_next' => $tradesUntilNext,
        ];
    }

    public function getLevelName(int $level): string
    {
        return match (max(1, min(self::MAX_LEVEL, $level))) {
            1 => 'Novice',
            2 => 'Active Trader',
            3 => 'Skilled Trader',
            4 => 'Expert Trader',
            default => 'Novice',
        };
    }

    public function getLevelColor(int $level): string
    {
        return match (max(1, min(self::MAX_LEVEL, $level))) {
            1 => 'gray',
            2 => 'amber',
            3 => 'cyan',
            4 => 'purple',
            default => 'gray',
        };
    }

    /**
     * Snapshot for profile / dashboard display.
     *
     * @return array{
     *   level: int,
     *   level_name: string,
     *   total_trades: int,
     *   min_trades_for_level: int,
     *   trades_for_next_level: int|null,
     *   trades_until_next: int|null,
     *   is_max_level: bool
     * }
     */
    public function getLevelProgressSnapshot(User $user): array
    {
        $user->refresh();
        $level = (int) ($user->level ?? 1);
        $totalTrades = $this->countUserTrades($user);
        $tradesForNext = $this->getTradesRequiredForNextLevel($level);
        $tradesUntilNext = $tradesForNext !== null ? max(0, $tradesForNext - $totalTrades) : null;

        return [
            'level' => $level,
            'level_name' => $this->getLevelName($level),
            'total_trades' => $totalTrades,
            'min_trades_for_level' => $this->getMinTradesForLevel($level),
            'trades_for_next_level' => $tradesForNext,
            'trades_until_next' => $tradesUntilNext,
            'is_max_level' => $level >= self::MAX_LEVEL,
        ];
    }
}
