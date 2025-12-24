<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAppPreference;
use Illuminate\Support\Facades\Cache;

class UserAppPreferencesService
{
    protected const CACHE_TTL = 1209600;

    protected const CACHE_PREFIX = 'loops:api:s:user_app_prefs';

    /**
     * Get user preferences (cached)
     */
    public function get(int $userId): UserAppPreference
    {
        $cacheKey = $this->getCacheKey($userId);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId) {
            $preferences = UserAppPreference::where('user_id', $userId)->first();

            if (! $preferences) {
                $preferences = $this->create($userId);
            }

            return $preferences->fresh();
        });
    }

    public function touch(int $userId, $migrate = false): void
    {
        if (! $migrate) {
            return;
        }

        UserAppPreference::upsert([
            [
                'user_id' => $userId,
                'autoplay_videos' => true,
                'loop_videos' => true,
                'data_saver_mode' => false,
                'default_feed' => 'local',
                'hide_for_you_feed' => false,
                'mute_on_open' => false,
                'lang' => 'en',
                'auto_expand_cw' => false,
                'appearance' => 'light',
                'reduce_motion' => false,
                'high_contrast' => false,
            ],
        ], uniqueBy: ['user_id'], update: []);
    }

    protected function create(int $userId): UserAppPreference
    {
        $user = User::find($userId);

        return UserAppPreference::create([
            'user_id' => $userId,
            'autoplay_videos' => true,
            'loop_videos' => true,
            'data_saver_mode' => false,
            'default_feed' => 'local',
            'hide_for_you_feed' => false,
            'mute_on_open' => false,
            'lang' => 'en',
            'auto_expand_cw' => false,
            'appearance' => 'light',
            'reduce_motion' => false,
            'high_contrast' => false,
        ]);
    }

    /**
     * Update user preferences and flush cache
     */
    public function update(int $userId, array $data): UserAppPreference
    {
        $preferences = UserAppPreference::updateOrCreate(
            ['user_id' => $userId],
            array_merge($this->getDefaults($userId), $data)
        );

        $this->flushCache($userId);

        return $preferences->fresh();
    }

    public function updateDisableForYouFeed()
    {
        UserAppPreference::chunkById(50, function ($prefs) {
            foreach ($prefs as $pref) {
                if ($pref->default_feed === 'forYou') {
                    $pref->update(['default_feed' => 'local']);
                }
            }
        }, 'user_id');
    }

    /**
     * Flush cache for a specific user
     */
    public function flushCache(int $userId): void
    {
        Cache::forget($this->getCacheKey($userId));
    }

    /**
     * Get cache key for user
     */
    protected function getCacheKey(int $userId): string
    {
        return self::CACHE_PREFIX.':'.$userId;
    }

    /**
     * Get default preferences for a user
     */
    protected function getDefaults(int $userId): array
    {
        $user = User::find($userId);

        return [
            'user_id' => $userId,
        ];
    }
}
