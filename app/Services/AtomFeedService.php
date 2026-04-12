<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class AtomFeedService
{
    const CACHE_KEY = 'api:s:atom:feed:byProfileId:';

    const CACHE_TTL = 86400;

    public static function getFeed(int $profileId): ?array
    {
        return Cache::remember(self::CACHE_KEY.$profileId, self::CACHE_TTL, function () use ($profileId) {
            $user = User::isActive()->where('profile_id', $profileId)->first();

            if (! $user || ! $user->has_atom) {
                return;
            }

            return $user->videos()
                ->publishedAndSafe()
                ->latest()
                ->take(20)
                ->get()
                ->map(fn ($v) => VideoService::getMediaData($v->id))
                ->filter()
                ->values()
                ->toArray();
        });
    }

    public static function invalidate(int $profileId): void
    {
        Cache::forget(self::CACHE_KEY.$profileId);
    }
}
