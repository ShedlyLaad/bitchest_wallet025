<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class TransactionCacheService
{
    private const IDS_KEY = 'user:%d:transactions:ids';
    private const DATA_KEY = 'user:%d:transactions:data';
    private const LIMIT = 200;

    public function store(int $userId, array $payload): void
    {
        if (empty($payload['cache_id'])) {
            return;
        }

        $cacheId = (string) $payload['cache_id'];
        $idsKey = sprintf(self::IDS_KEY, $userId);
        $dataKey = sprintf(self::DATA_KEY, $userId);

        Redis::hset($dataKey, $cacheId, json_encode($payload));
        Redis::lpush($idsKey, $cacheId);
        Redis::ltrim($idsKey, 0, self::LIMIT - 1);

        $this->cleanup($userId);
    }

    public function update(int $userId, string $cacheId, array $payload): void
    {
        $dataKey = sprintf(self::DATA_KEY, $userId);
        Redis::hset($dataKey, $cacheId, json_encode($payload));
    }

    public function getRecent(int $userId, int $limit): array
    {
        $idsKey = sprintf(self::IDS_KEY, $userId);
        $dataKey = sprintf(self::DATA_KEY, $userId);

        $ids = Redis::lrange($idsKey, 0, max(0, $limit - 1));
        if (empty($ids)) {
            return [];
        }

        $raw = Redis::hmget($dataKey, $ids);
        $items = [];

        foreach ($raw as $item) {
            if ($item) {
                $decoded = json_decode($item, true);
                if (is_array($decoded)) {
                    $items[] = $decoded;
                }
            }
        }

        return $items;
    }

    private function cleanup(int $userId): void
    {
        $idsKey = sprintf(self::IDS_KEY, $userId);
        $dataKey = sprintf(self::DATA_KEY, $userId);

        $ids = Redis::lrange($idsKey, 0, self::LIMIT - 1);
        if (empty($ids)) {
            return;
        }

        $existing = Redis::hkeys($dataKey);
        $toRemove = array_diff($existing, $ids);
        if (!empty($toRemove)) {
            Redis::hdel($dataKey, ...$toRemove);
        }
    }
}
