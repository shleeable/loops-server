<?php

namespace App\Services;

use App\Models\User;
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserActivityService
{
    public function markActive(User $user, ?Carbon $ts = null): void
    {
        $ts = ($ts ?? Carbon::now('UTC'))->toImmutable();
        $ymd = $ts->format('Ymd');
        $key = "uas:{$user->id}:{$ymd}";

        $ttl = (int) $ts->diffInSeconds($ts->endOfDay());

        Cache::remember($key, $ttl, function () use ($ts, $user) {
            $user->forceFill(['last_active_at' => $ts])->saveQuietly();

            DB::table('user_daily_actives')->insertOrIgnore([
                'day' => $ts->toDateString(),
                'user_id' => $user->id,
            ]);

            return true;
        });
    }
}
