<?php

namespace App\Services;

use App\Models\Follower;
use Illuminate\Support\Facades\Cache;

class FollowerService
{
    public const FOLLOWS_KEY = 'api:s:follower:';

    public static function follows($aid, $pid)
    {
        return Cache::remember(self::FOLLOWS_KEY.$aid.':'.$pid, now()->addHours(6), function () use ($aid, $pid) {
            return Follower::whereProfileId($aid)->whereFollowingId($pid)->exists();
        });
    }

    public static function del($aid, $pid)
    {
        Cache::forget(self::FOLLOWS_KEY.$aid.':'.$pid);
        Cache::forget(self::FOLLOWS_KEY.$pid.':'.$aid);
    }
}
