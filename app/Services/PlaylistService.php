<?php

namespace App\Services;

use App\Models\Profile;

class PlaylistService
{
    /**
     * Tiers ordered highest to lowest.
     * Each entry: [min_followers, min_videos, max_playlists]
     */
    private const TIERS = [
        // [min_followers, min_videos, max_playlists]
        [2000,  5, 100],
        [500,   5, 30],
        [100,   5, 15],
        [50,    5, 10],
        [5,     2,  3],
        [0,     0,  1],
    ];

    private const BASE_LIMIT = 0;

    public static function getMaxPlaylists(Profile $profile): int
    {
        $followers = $profile->followers ?? 0;
        $videos = $profile->video_count ?? 0;

        foreach (self::TIERS as [$minFollowers, $minVideos, $limit]) {
            if ($followers >= $minFollowers && $videos >= $minVideos) {
                return $limit;
            }
        }

        return self::BASE_LIMIT;
    }

    public static function getMinThreshold(): int
    {
        return self::TIERS[array_key_last(self::TIERS)][0];
    }

    public static function canCreatePlaylist(Profile $profile): bool
    {
        return $profile->playlists()->count() < self::getMaxPlaylists($profile);
    }

    public static function getRemainingSlots(Profile $profile): int
    {
        return max(0, self::getMaxPlaylists($profile) - $profile->playlists()->count());
    }

    public static function getDetails(Profile $profile): array
    {
        $canCreate = self::canCreatePlaylist($profile);
        $slots = self::getRemainingSlots($profile);
        $maxLimit = self::getMaxPlaylists($profile);
        $restricted = $profile->can_playlist === false;

        return [
            'feature_unavailable' => $restricted,
            'can_create' => $restricted ? false : $canCreate,
            'max_limit' => $canCreate ? $maxLimit : 0,
            'slots_available' => $canCreate ? $slots : 0,
        ];
    }
}
