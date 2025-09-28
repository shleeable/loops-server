<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class UserFilterService
{
    public static function getFilters($profileId)
    {
        return DB::table('user_filters')
            ->select('account_id')
            ->where('profile_id', $profileId);
    }

    public static function isBlocking($pid, $targetId)
    {
        if (DB::table('user_filters')
            ->where('profile_id', $targetId)
            ->where('account_id', $pid)
            ->exists()
        ) {
            return true;
        }

        return DB::table('user_filters')
            ->where('profile_id', $pid)
            ->where('account_id', $targetId)
            ->exists();
    }

    public static function isBlockedBy($pid, $targetId)
    {
        return DB::table('user_filters')
            ->where('profile_id', $pid)
            ->where('account_id', $targetId)
            ->exists();
    }
}
