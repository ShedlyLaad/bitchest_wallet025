<?php

namespace App\Services;

use App\Models\Portfolio;
use App\Models\Transaction;
use Illuminate\Support\Facades\Redis;

class AccountCacheService
{
    private const BALANCE_KEY = 'user:%d:balance';
    private const HOLDING_KEY = 'user:%d:crypto:%d:quantity';
    private const TTL_SECONDS = 3600;

    public function getBalance(int $userId, ?float $fallback = null): ?float
    {
        $key = sprintf(self::BALANCE_KEY, $userId);
        $cached = Redis::get($key);

        if ($cached !== null) {
            return (float) $cached;
        }

        if ($fallback !== null) {
            $this->setBalance($userId, $fallback);
            return $fallback;
        }

        return null;
    }

    public function setBalance(int $userId, float $balance): void
    {
        $key = sprintf(self::BALANCE_KEY, $userId);
        Redis::setex($key, self::TTL_SECONDS, (string) $balance);
    }

    public function getCryptoQuantity(int $userId, int $cryptoId, ?float $fallback = null): ?float
    {
        $key = sprintf(self::HOLDING_KEY, $userId, $cryptoId);
        $cached = Redis::get($key);

        if ($cached !== null) {
            return (float) $cached;
        }

        if ($fallback !== null) {
            $this->setCryptoQuantity($userId, $cryptoId, $fallback);
            return $fallback;
        }

        return null;
    }

    public function setCryptoQuantity(int $userId, int $cryptoId, float $quantity): void
    {
        $key = sprintf(self::HOLDING_KEY, $userId, $cryptoId);
        Redis::setex($key, self::TTL_SECONDS, (string) max(0, $quantity));
    }

    public function getCryptoQuantityOrCompute(int $userId, int $cryptoId): float
    {
        $cached = $this->getCryptoQuantity($userId, $cryptoId);
        if ($cached !== null) {
            return $cached;
        }

        $portfolio = Portfolio::where('user_id', $userId)
            ->where('crypto_currency_id', $cryptoId)
            ->first();

        if (!$portfolio) {
            $this->setCryptoQuantity($userId, $cryptoId, 0.0);
            return 0.0;
        }

        $totalBuy = (float) Transaction::where('portfolio_id', $portfolio->id)
            ->where('type', 'buy')
            ->sum('quantity');
        $totalSell = (float) Transaction::where('portfolio_id', $portfolio->id)
            ->where('type', 'sell')
            ->sum('quantity');

        $quantity = max(0.0, $totalBuy - $totalSell);
        $this->setCryptoQuantity($userId, $cryptoId, $quantity);

        return $quantity;
    }
}
