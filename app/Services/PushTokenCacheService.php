<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class PushTokenCacheService
{
    protected const HASH_KEY = 'loops:push_tokens:profile_map';

    protected const EMPTY_FLAG = 'loops:push_tokens:no_accounts';

    protected const EMPTY_FLAG_TTL = 60 * 30;

    public function has(string $profileId): bool
    {
        $this->ensureLoaded();

        return (bool) Redis::hexists(self::HASH_KEY, $profileId);
    }

    public function get(string $profileId): ?string
    {
        $this->ensureLoaded();

        $token = Redis::hget(self::HASH_KEY, $profileId);

        return $token ?: null;
    }

    public function add(string $profileId, string $token): void
    {
        Redis::hset(self::HASH_KEY, $profileId, $token);
        Cache::forget(self::EMPTY_FLAG);
    }

    public function remove(string $profileId): void
    {
        Redis::hdel(self::HASH_KEY, $profileId);
    }

    public function count(): int
    {
        return (int) Redis::hlen(self::HASH_KEY);
    }

    public function all(): array
    {
        $this->ensureLoaded();

        return Redis::hgetall(self::HASH_KEY) ?: [];
    }

    public function regenerate(): int
    {
        Redis::del(self::HASH_KEY);
        Cache::forget(self::EMPTY_FLAG);

        return $this->loadFromDatabase();
    }

    public function flush(): void
    {
        Redis::del(self::HASH_KEY);
        Cache::forget(self::EMPTY_FLAG);
    }

    protected function ensureLoaded(): void
    {
        if (Redis::exists(self::HASH_KEY)) {
            return;
        }

        if (Cache::has(self::EMPTY_FLAG)) {
            return;
        }

        $this->loadFromDatabase();
    }

    protected function loadFromDatabase(): int
    {
        $count = 0;

        User::where('status', 1)
            ->whereNotNull('push_token')
            ->where('push_token', '!=', '')
            ->select(['id', 'status', 'profile_id', 'push_token'])
            ->chunkById(1000, function ($users) use (&$count) {
                $map = [];
                foreach ($users as $user) {
                    if (str_starts_with($user->push_token, 'ExponentPushToken[')) {
                        $map[$user->profile_id] = $user->push_token;
                    }
                }

                if (! empty($map)) {
                    Redis::hmset(self::HASH_KEY, $map);
                    $count += count($map);
                }
            });

        if ($count === 0) {
            Cache::put(self::EMPTY_FLAG, true, self::EMPTY_FLAG_TTL);

            if (config('logging.dev_log')) {
                Log::info('PushTokenCache: No push token accounts found, flagging empty for 30m');
            }
        } else {
            if (config('logging.dev_log')) {
                Log::info('PushTokenCache: Loaded tokens from database', [
                    'count' => $count,
                ]);
            }
        }

        return $count;
    }
}
