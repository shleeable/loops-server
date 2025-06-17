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
            ->whereProfileId($targetId)
            ->whereAccountId($pid)
            ->exists()
        ) {
            return true;
        }

        return DB::table('user_filters')
            ->whereProfileId($pid)
            ->whereAccountId($targetId)
            ->exists();
    }
}
