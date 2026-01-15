<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class NotificationCacheService
{
    private const IDS_KEY = 'user:%d:notifications:ids';
    private const DATA_KEY = 'user:%d:notifications:data';
    private const UNREAD_KEY = 'user:%d:notifications:unread';
    private const LIMIT = 100;

    public function store(int $userId, array $payload): void
    {
        if (empty($payload['id'])) {
            return;
        }

        $notificationId = (string) $payload['id'];
        $idsKey = sprintf(self::IDS_KEY, $userId);
        $dataKey = sprintf(self::DATA_KEY, $userId);

        Redis::hset($dataKey, $notificationId, json_encode($payload));
        Redis::lpush($idsKey, $notificationId);
        Redis::ltrim($idsKey, 0, self::LIMIT - 1);

        if (empty($payload['is_read'])) {
            Redis::incr(sprintf(self::UNREAD_KEY, $userId));
        }

        $this->cleanup($userId);
    }

    public function getRecent(int $userId, int $limit, bool $unreadOnly = false): array
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
                    if ($unreadOnly && !empty($decoded['is_read'])) {
                        continue;
                    }
                    $items[] = $decoded;
                }
            }
        }

        return $items;
    }

    public function getUnreadCount(int $userId): ?int
    {
        $key = sprintf(self::UNREAD_KEY, $userId);
        $value = Redis::get($key);
        return $value !== null ? (int) $value : null;
    }

    public function markAsRead(int $userId, int $notificationId): void
    {
        $dataKey = sprintf(self::DATA_KEY, $userId);
        $key = (string) $notificationId;

        $raw = Redis::hget($dataKey, $key);
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && empty($decoded['is_read'])) {
                $decoded['is_read'] = true;
                $decoded['read_at'] = now()->toISOString();
                Redis::hset($dataKey, $key, json_encode($decoded));
                $unreadKey = sprintf(self::UNREAD_KEY, $userId);
                $current = (int) Redis::get($unreadKey);
                Redis::set($unreadKey, max(0, $current - 1));
            }
        }
    }

    public function markAllAsRead(int $userId): void
    {
        $idsKey = sprintf(self::IDS_KEY, $userId);
        $dataKey = sprintf(self::DATA_KEY, $userId);

        $ids = Redis::lrange($idsKey, 0, self::LIMIT - 1);
        if (empty($ids)) {
            Redis::set(sprintf(self::UNREAD_KEY, $userId), 0);
            return;
        }

        $raw = Redis::hmget($dataKey, $ids);
        foreach ($ids as $index => $id) {
            $item = $raw[$index] ?? null;
            if ($item) {
                $decoded = json_decode($item, true);
                if (is_array($decoded) && empty($decoded['is_read'])) {
                    $decoded['is_read'] = true;
                    $decoded['read_at'] = now()->toISOString();
                    Redis::hset($dataKey, $id, json_encode($decoded));
                }
            }
        }

        Redis::set(sprintf(self::UNREAD_KEY, $userId), 0);
    }

    public function remove(int $userId, int $notificationId): void
    {
        $idsKey = sprintf(self::IDS_KEY, $userId);
        $dataKey = sprintf(self::DATA_KEY, $userId);

        $key = (string) $notificationId;
        $raw = Redis::hget($dataKey, $key);
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && empty($decoded['is_read'])) {
                $unreadKey = sprintf(self::UNREAD_KEY, $userId);
                $current = (int) Redis::get($unreadKey);
                Redis::set($unreadKey, max(0, $current - 1));
            }
        }

        Redis::hdel($dataKey, $key);
        Redis::lrem($idsKey, 0, $key);
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
