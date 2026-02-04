<?php

namespace App\Services;

use App\Models\Instance;
use Illuminate\Support\Facades\Cache;

class InstanceService
{
    const CACHE_STATS_KEY = 'loops:admin:api:instance-stats';

    const CACHE_ADVANCED_STATS_KEY = 'loops:admin:api:instance-advanced-stats';

    public function flushStats()
    {
        $this->getStats(true);
        $this->getAdvancedStats(true);

        return 1;
    }

    public function getStats($flush = false)
    {
        if ($flush) {
            Cache::forget(self::CACHE_STATS_KEY);
        }

        return Cache::remember(self::CACHE_STATS_KEY, now()->addHours(12), function () {
            return [
                [
                    'name' => 'Total',
                    'value' => Instance::active()->count(),
                ],
                [
                    'name' => 'New',
                    'value' => Instance::active()->where('created_at', '>', now()->subHours(24))->count(),
                ],
                [
                    'name' => 'Blocked',
                    'value' => Instance::whereFederationState(2)->count(),
                ],
                [
                    'name' => 'Users',
                    'value' => Instance::active()->sum('user_count'),
                ],
                [
                    'name' => 'Videos',
                    'value' => Instance::active()->sum('video_count'),
                ],
                [
                    'name' => 'Followers',
                    'value' => Instance::active()->sum('follower_count'),
                ],
                [
                    'name' => 'Following',
                    'value' => Instance::active()->sum('following_count'),
                ],
                [
                    'name' => 'Comments',
                    'value' => Instance::active()->sum('comment_count'),
                ],
            ];
        });
    }

    public function getAdvancedStats($flush = false)
    {
        if ($flush) {
            Cache::forget(self::CACHE_ADVANCED_STATS_KEY);
        }

        return Cache::remember(self::CACHE_ADVANCED_STATS_KEY, now()->addDays(7), function () {
            $totalInstances = Instance::active()->count();
            $activeInstances = Instance::active()->count();
            $allowedVideoPosts = Instance::active()->where('allow_video_posts', true)->count();
            $allowedInFyf = Instance::active()->where('allow_videos_in_fyf', true)->count();

            $softwareStats = Instance::select('software')
                ->selectRaw('COUNT(*) as count')
                ->selectRaw('SUM(CASE WHEN allow_video_posts = 1 THEN 1 ELSE 0 END) as allow_video_posts_count')
                ->where('is_blocked', false)
                ->active()
                ->groupBy('software')
                ->orderByDesc('count')
                ->get();

            return [
                'data' => [
                    'stats' => [
                        ['name' => 'Total Instances', 'value' => $totalInstances],
                        ['name' => 'Active Instances', 'value' => $activeInstances],
                        ['name' => 'Video Posts Allowed', 'value' => $allowedVideoPosts],
                        ['name' => 'FYF Allowed', 'value' => $allowedInFyf],
                    ],
                    'software_stats' => $softwareStats,
                ],
            ];
        });
    }
}
