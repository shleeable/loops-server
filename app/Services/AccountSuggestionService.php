<?php

namespace App\Services;

use App\Models\HiddenSuggestion;
use App\Models\Profile;
use App\Models\UserInterest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class AccountSuggestionService
{
    const CACHE_TTL = 86400;

    const LIMIT = 20;

    const INTEREST_TOP_K = 25;

    const CANDIDATE_POOL = 80;

    /**
     * Get suggested accounts for a profile
     */
    public static function get(int $profileId, int $limit = self::LIMIT): array
    {
        $cacheKey = self::getCacheKey($profileId);

        $cached = self::getFromCache($cacheKey, $limit, $profileId);
        if ($cached !== null) {
            return $cached;
        }

        return self::regenerate($profileId, $limit);
    }

    /**
     * Regenerate suggestions for a profile
     */
    public static function regenerate(int $profileId, int $limit = self::LIMIT): array
    {
        $scores = self::calculateSuggestions($profileId);

        if ($scores->isEmpty()) {
            return [];
        }

        $cacheKey = self::getCacheKey($profileId);
        $redis = Redis::connection();

        $redis->del($cacheKey);

        foreach ($scores as $score) {
            $redis->zadd($cacheKey, $score->total_score, $score->id);
        }

        $redis->expire($cacheKey, self::CACHE_TTL);

        return self::getFromCache($cacheKey, $limit, $profileId) ?? [];
    }

    /**
     * Hide a suggestion for a user
     */
    public static function hide(int $profileId, int $accountId): void
    {
        HiddenSuggestion::hide($profileId, $accountId);

        self::removeForUser($profileId, $accountId);
    }

    /**
     * Unhide a suggestion for a user
     */
    public static function unhide(int $profileId, int $accountId): void
    {
        HiddenSuggestion::unhide($profileId, $accountId);

        self::invalidate($profileId);
    }

    /**
     * Check if a suggestion is hidden
     */
    public static function isHidden(int $profileId, int $accountId): bool
    {
        return HiddenSuggestion::isHidden($profileId, $accountId);
    }

    /**
     * Remove a profile from all suggestion caches
     */
    public static function removeFromAll(int $profileId): void
    {
        $pattern = self::getCacheKey('*');
        $redis = Redis::connection();

        $keys = $redis->keys($pattern);

        foreach ($keys as $key) {
            $redis->zrem($key, (string) $profileId);
        }
    }

    /**
     * Remove a profile from a specific user's suggestions
     */
    public static function removeForUser(int $userId, int $profileIdToRemove): void
    {
        $cacheKey = self::getCacheKey($userId);
        Redis::connection()->zrem($cacheKey, (string) $profileIdToRemove);
    }

    /**
     * Remove multiple profiles from a user's suggestions
     */
    public static function removeMultipleForUser(int $userId, array $profileIds): void
    {
        if (empty($profileIds)) {
            return;
        }

        $cacheKey = self::getCacheKey($userId);
        $redis = Redis::connection();

        foreach ($profileIds as $profileId) {
            $redis->zrem($cacheKey, $profileId);
        }
    }

    /**
     * Invalidate suggestions for a user
     */
    public static function invalidate(int $profileId): void
    {
        $cacheKey = self::getCacheKey($profileId);
        Redis::connection()->del($cacheKey);
    }

    /**
     * Warm cache for a user (useful for background jobs)
     */
    public static function warmCache(int $profileId): void
    {
        self::regenerate($profileId);
    }

    /**
     * Get suggestions from cache and hydrate
     */
    protected static function getFromCache(string $cacheKey, int $limit, int $profileId): ?array
    {
        $redis = Redis::connection();

        if (! $redis->exists($cacheKey)) {
            return null;
        }

        $hiddenIds = HiddenSuggestion::where('profile_id', $profileId)
            ->pluck('account_id')
            ->toArray();

        $fetchLimit = min($limit * 2 + count($hiddenIds) + 10, self::CANDIDATE_POOL);
        $profileIds = $redis->zrevrange($cacheKey, 0, $fetchLimit - 1);

        if (empty($profileIds)) {
            return null;
        }

        $profileIds = array_diff($profileIds, $hiddenIds);

        $accounts = collect($profileIds)
            ->map(fn ($id) => AccountService::get($id))
            ->filter()
            ->take($limit)
            ->values()
            ->all();

        return $accounts;
    }

    /**
     * Calculate suggestion scores
     */
    protected static function calculateSuggestions(int $profileId): \Illuminate\Support\Collection
    {
        $myInterestsSub = UserInterest::query()
            ->where('profile_id', $profileId)
            ->where('score', '>', 0)
            ->orderByDesc('score')
            ->limit(self::INTEREST_TOP_K)
            ->select(['interest_type', 'interest_value', 'score']);

        $interestMatchSub = DB::table('user_interests as ui_cand')
            ->joinSub($myInterestsSub, 'ui_me', function ($join) {
                $join->on('ui_cand.interest_type', '=', 'ui_me.interest_type')
                    ->on('ui_cand.interest_value', '=', 'ui_me.interest_value');
            })
            ->selectRaw('ui_cand.profile_id as candidate_id')
            ->selectRaw('SUM(ui_me.score * ui_cand.score) as interest_score')
            ->selectRaw('COUNT(*) as interest_matches')
            ->groupBy('ui_cand.profile_id');

        $socialSub = DB::table('followers as f1')
            ->join('followers as f2', 'f2.profile_id', '=', 'f1.following_id')
            ->where('f1.profile_id', $profileId)
            ->selectRaw('f2.following_id as candidate_id')
            ->selectRaw('COUNT(DISTINCT f2.profile_id) as social_score')
            ->groupBy('f2.following_id');

        return Profile::query()
            ->active()
            ->whereLocal(true)
            ->whereNotNull('profiles.bio')
            ->where('profiles.discoverable', true)
            ->where('profiles.followers', '>=', 2)
            ->where('profiles.video_count', '>=', 2)
            ->where('profiles.id', '!=', $profileId)

            ->leftJoin('followers as already', function ($join) use ($profileId) {
                $join->on('already.following_id', '=', 'profiles.id')
                    ->where('already.profile_id', '=', $profileId);
            })
            ->whereNull('already.following_id')

            ->leftJoin('user_filters as uf', function ($join) use ($profileId) {
                $join->on('uf.account_id', '=', 'profiles.id')
                    ->where('uf.profile_id', '=', $profileId);
            })
            ->whereNull('uf.account_id')

            ->leftJoin('hidden_suggestions as hs', function ($join) use ($profileId) {
                $join->on('hs.account_id', '=', 'profiles.id')
                    ->where('hs.profile_id', '=', $profileId);
            })
            ->whereNull('hs.account_id')

            ->leftJoinSub($interestMatchSub, 'im', function ($join) {
                $join->on('im.candidate_id', '=', 'profiles.id');
            })
            ->leftJoinSub($socialSub, 'sm', function ($join) {
                $join->on('sm.candidate_id', '=', 'profiles.id');
            })

            ->select('profiles.id')
            ->selectRaw('COALESCE(im.interest_score, 0) as interest_score')
            ->selectRaw('COALESCE(im.interest_matches, 0) as interest_matches')
            ->selectRaw('COALESCE(sm.social_score, 0) as social_score')
            ->selectRaw('LOG10(profiles.followers + 1) as popularity_score')
            ->selectRaw('LOG10(profiles.video_count + 1) as creator_activity_score')
            ->selectRaw('
                (COALESCE(im.interest_score, 0) * 0.60) +
                (COALESCE(sm.social_score, 0) * 0.30) +
                (LOG10(profiles.followers + 1) * 0.07) +
                (LOG10(profiles.video_count + 1) * 0.03)
                as total_score
            ')
            ->orderByDesc('total_score')
            ->limit(self::CANDIDATE_POOL)
            ->get();
    }

    /**
     * Get cache key for a profile
     */
    protected static function getCacheKey($profileId): string
    {
        return "loops:api:account:suggestions:{$profileId}";
    }
}
