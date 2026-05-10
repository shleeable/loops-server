<?php

namespace App\Services;

use App\Models\UserFilter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserFilterService
{
    public const ALL_CACHE_KEY = 'api:s:userfilter:v1:';

    public static function getFilters($profileId)
    {
        return DB::table('user_filters')
            ->select('account_id')
            ->where('profile_id', $profileId);
    }

    public static function isBlocking($pid, $targetId)
    {
        return in_array($targetId, self::getAll($pid))
            || in_array($pid, self::getAll($targetId));
    }

    public static function isBlockedBy($pid, $targetId)
    {
        return DB::table('user_filters')
            ->where('profile_id', $pid)
            ->where('account_id', $targetId)
            ->exists();
    }

    public static function getAll($profileId, $refresh = false)
    {
        if ($refresh) {
            Cache::forget(self::ALL_CACHE_KEY.$profileId);
        }

        return Cache::remember(
            self::ALL_CACHE_KEY.$profileId,
            now()->addHours(48),
            fn () => UserFilter::where('profile_id', $profileId)
                ->pluck('account_id')
                ->all()
        );
    }
}
