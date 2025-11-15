<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\CommentReply;
use App\Models\CommentReplyLike;
use App\Models\Follower;
use App\Models\Hashtag;
use App\Models\Instance;
use App\Models\Profile;
use App\Models\Report;
use App\Models\User;
use App\Models\Video;
use App\Models\VideoLike;
use App\Models\VideoRepost;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    /**
     * Cache key base + version so you can bump when structure changes.
     */
    protected string $baseCacheKey = 'admin_dashboard_stats_';

    public const CACHE_VERSION = 'v1.3';

    /**
     * Main entrypoint for controller & command.
     *
     * @param  string  $periodParam  e.g. '30d'
     * @param  bool  $forceRefresh  whether to clear cache first
     */
    public function getDashboardData(string $periodParam = '30d', bool $forceRefresh = false): array
    {
        $period = $this->validateAndParsePeriod($periodParam);
        $cacheKey = "{$this->baseCacheKey}{$periodParam}:".self::CACHE_VERSION;

        $data = [
            'refresh' => $forceRefresh,
        ];

        if ($forceRefresh) {
            Cache::forget('admin_dashboard_stats_partial:total_likes');
            Cache::forget('admin_dashboard_stats_partial:total_comments');
            Cache::forget('admin_dashboard_stats_partial:total_follows');
            Cache::forget('admin_dashboard_stats_partial:pending_reports');
            Cache::forget('admin_dashboard_stats_partial:active_users_today');
            Cache::forget('admin_dashboard_stats_partial:storage_used');
            Cache::forget("{$this->baseCacheKey}partial:recent_activity");
            Cache::forget("{$this->baseCacheKey}partial:federation");
            Cache::forget("{$this->baseCacheKey}partial:top_hashtags");
            Cache::forget($cacheKey);
        }

        // Partials
        $data['recent_activity'] = Cache::remember(
            "{$this->baseCacheKey}partial:recent_activity",
            now()->addMinutes(15),
            fn () => $this->getRecentActivity()
        );

        $data['federation'] = Cache::remember(
            "{$this->baseCacheKey}partial:federation",
            now()->addHours(4),
            fn () => $this->getFederationStats()
        );

        $data['top_hashtags'] = Cache::remember(
            "{$this->baseCacheKey}partial:top_hashtags",
            now()->addHours(12),
            fn () => $this->getTopHashtags()
        );

        // Big dashboard payload
        $dashboardData = Cache::remember(
            $cacheKey,
            $period['ttl'],
            function () use ($period) {
                return [
                    'cached_at' => now(),
                    'period' => $period['label'],
                    'metrics' => $this->getKeyMetrics($period['days']),
                    'charts' => [
                        'user_growth' => $this->getUserGrowthData($period),
                        'video_uploads' => $this->getVideoUploadsData($period),
                        'engagement' => $this->getEngagementData($period['chart_days']),
                        'content_distribution' => $this->getContentDistribution(),
                    ],
                    'moderation' => $this->getModerationStats($period['days']),
                ];
            }
        );

        $data = array_merge($data, $dashboardData);

        // Live-ish partial metrics on top of cached metrics
        $data['metrics']['storage_used'] = $this->getStorageUsed();
        $data['metrics']['active_today'] = $this->getActiveToday();
        $data['metrics']['pending_reports'] = $this->getPendingReports();
        $data['metrics']['total_comments'] = $this->getCommentCount();
        $data['metrics']['total_follows'] = $this->getTotalFollows();
        $data['metrics']['total_likes'] = $this->getTotalLikes();

        return $data;
    }

    /**
     * Convenience for the scheduled command: force-refresh only 30d.
     */
    public function warmThirtyDayDashboard(): array
    {
        return $this->getDashboardData('30d', true);
    }

    protected function validateAndParsePeriod(string $period): array
    {
        $validPeriods = [
            '30d' => [
                'days' => 30,
                'label' => 'Last 30 Days',
                'chart_days' => 30,
                'grouping' => 'daily',
                'ttl' => now()->addHours(12),
            ],
            '60d' => [
                'days' => 60,
                'label' => 'Last 60 Days',
                'chart_days' => 60,
                'grouping' => 'daily',
                'ttl' => now()->addDays(2),
            ],
            '90d' => [
                'days' => 90,
                'label' => 'Last 90 Days',
                'chart_days' => 30,
                'grouping' => 'weekly',
                'ttl' => now()->addDays(12),
            ],
            '365d' => [
                'days' => 365,
                'label' => 'Last 365 Days',
                'chart_days' => 12,
                'grouping' => 'monthly',
                'ttl' => now()->addDays(20),
            ],
        ];

        return $validPeriods[$period] ?? $validPeriods['30d'];
    }

    protected function getStorageUsed()
    {
        return Cache::remember(
            'admin_dashboard_stats_partial:storage_used',
            now()->addHours(12),
            function () {
                $storageKB = Video::sum('size_kb');
                $storageGB = round($storageKB / 1024 / 1024, 2);

                return $storageGB.' GB';
            }
        );
    }

    protected function getActiveToday()
    {
        return Cache::remember(
            'admin_dashboard_stats_partial:active_users_today',
            now()->addMinutes(15),
            function () {
                return DB::table('user_daily_actives')
                    ->where('day', now()->toDateString())
                    ->distinct('user_id')
                    ->count('user_id');
            }
        );
    }

    protected function getPendingReports()
    {
        return Cache::remember(
            'admin_dashboard_stats_partial:pending_reports',
            now()->addMinutes(15),
            fn () => Report::where('admin_seen', false)->count()
        );
    }

    protected function getCommentCount()
    {
        return Cache::remember(
            'admin_dashboard_stats_partial:total_comments',
            now()->addHours(4),
            fn () => Comment::count()
        );
    }

    protected function getTotalFollows()
    {
        return Cache::remember(
            'admin_dashboard_stats_partial:total_follows',
            now()->addMinutes(30),
            fn () => Follower::count()
        );
    }

    protected function getTotalLikes()
    {
        return Cache::remember(
            'admin_dashboard_stats_partial:total_likes',
            now()->addHours(4),
            fn () => VideoLike::count()
                + CommentLike::count()
                + CommentReplyLike::count()
        );
    }

    protected function getKeyMetrics(int $days = 30): array
    {
        $now = Carbon::now();
        $periodStart = $now->copy()->subDays($days);
        $comparisonStart = $now->copy()->subDays($days * 2);

        $totalUsers = User::count();
        $usersThisPeriod = User::where('created_at', '>=', $periodStart)->count();
        $usersPreviousPeriod = User::whereBetween('created_at', [$comparisonStart, $periodStart])->count();
        $usersChange = $this->calculatePercentageChange($usersThisPeriod, $usersPreviousPeriod);

        $totalVideos = Video::count();
        $videosThisPeriod = Video::where('created_at', '>=', $periodStart)->count();
        $videosPreviousPeriod = Video::whereBetween('created_at', [$comparisonStart, $periodStart])->count();
        $videosChange = $this->calculatePercentageChange($videosThisPeriod, $videosPreviousPeriod);

        $totalInteractions = VideoLike::count()
            + Comment::count()
            + CommentReply::count()
            + VideoRepost::count()
            + CommentLike::count()
            + CommentReplyLike::count();

        $engagementRate = $totalVideos > 0
            ? round(($totalInteractions / $totalVideos) * 100, 1)
            : 0;

        $interactionsThisPeriod = VideoLike::where('created_at', '>=', $periodStart)->count()
            + Comment::where('created_at', '>=', $periodStart)->count()
            + CommentReply::where('created_at', '>=', $periodStart)->count()
            + VideoRepost::where('created_at', '>=', $periodStart)->count()
            + CommentLike::where('created_at', '>=', $periodStart)->count()
            + CommentReplyLike::where('created_at', '>=', $periodStart)->count();

        $videosThisPeriodCount = max($videosThisPeriod, 1);
        $engagementRateThisPeriod = round(($interactionsThisPeriod / $videosThisPeriodCount) * 100, 1);

        $interactionsPreviousPeriod = VideoLike::whereBetween('created_at', [$comparisonStart, $periodStart])->count()
            + Comment::whereBetween('created_at', [$comparisonStart, $periodStart])->count()
            + CommentReply::whereBetween('created_at', [$comparisonStart, $periodStart])->count()
            + VideoRepost::whereBetween('created_at', [$comparisonStart, $periodStart])->count()
            + CommentLike::whereBetween('created_at', [$comparisonStart, $periodStart])->count()
            + CommentReplyLike::whereBetween('created_at', [$comparisonStart, $periodStart])->count();

        $videosPreviousPeriodCount = max($videosPreviousPeriod, 1);
        $engagementRatePreviousPeriod = round(($interactionsPreviousPeriod / $videosPreviousPeriodCount) * 100, 1);

        $engagementChange = $this->calculatePercentageChange(
            $engagementRateThisPeriod,
            $engagementRatePreviousPeriod
        );

        $activeInstances = Instance::where('is_blocked', false)
            ->where('is_silenced', false)
            ->count();

        $instancesThisPeriod = Instance::where('created_at', '>=', $periodStart)->count();
        $instancesPreviousPeriod = Instance::whereBetween('created_at', [$comparisonStart, $periodStart])->count();
        $instancesChange = $this->calculatePercentageChange($instancesThisPeriod, $instancesPreviousPeriod);

        return [
            'total_users' => $totalUsers,
            'users_change' => $usersChange,
            'total_videos' => $totalVideos,
            'videos_change' => $videosChange,
            'engagement_rate' => $engagementRate,
            'engagement_change' => $engagementChange,
            'active_instances' => $activeInstances,
            'instances_change' => $instancesChange,
            'active_today' => null,
            'total_comments' => null,
            'total_likes' => null,
            'total_follows' => null,
            'pending_reports' => null,
            'storage_used' => null,
        ];
    }

    protected function getUserGrowthData(array $period): array
    {
        $dates = [];
        $values = [];
        $days = $period['days'];
        $grouping = $period['grouping'];

        if ($grouping === 'monthly') {
            for ($i = 11; $i >= 0; $i--) {
                $startOfMonth = Carbon::now()->subMonths($i)->startOfMonth();
                $endOfMonth = Carbon::now()->subMonths($i)->endOfMonth();

                $dates[] = $startOfMonth->format('M Y');

                $count = User::whereBetween('created_at', [
                    $startOfMonth->toDateString(),
                    $endOfMonth->toDateString(),
                ])->count();

                $values[] = $count;
            }
        } elseif ($grouping === 'weekly') {
            $dataPoints = 30;
            for ($i = $dataPoints - 1; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i * 7);
                $endDate = $date->copy()->addDays(6);

                $dates[] = $date->format('M d');

                $count = User::whereBetween('created_at', [
                    $date->toDateString(),
                    $endDate->toDateString(),
                ])->count();

                $values[] = $count;
            }
        } else {
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $dates[] = $date->format('M d');

                $count = User::whereDate('created_at', $date->toDateString())->count();
                $values[] = $count;
            }
        }

        return [
            'dates' => $dates,
            'values' => $values,
        ];
    }

    protected function getVideoUploadsData(array $period): array
    {
        $dates = [];
        $values = [];
        $days = $period['days'];
        $grouping = $period['grouping'];

        if ($grouping === 'monthly') {
            for ($i = 11; $i >= 0; $i--) {
                $startOfMonth = Carbon::now()->subMonths($i)->startOfMonth();
                $endOfMonth = Carbon::now()->subMonths($i)->endOfMonth();

                $dates[] = $startOfMonth->format('M Y');

                $count = Video::whereBetween('created_at', [
                    $startOfMonth->toDateString(),
                    $endOfMonth->toDateString(),
                ])
                    ->where('is_local', true)
                    ->count();

                $values[] = $count;
            }
        } elseif ($grouping === 'weekly') {
            $dataPoints = 30;
            for ($i = $dataPoints - 1; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i * 7);
                $endDate = $date->copy()->addDays(6);

                $dates[] = $date->format('M d');

                $count = Video::whereBetween('created_at', [
                    $date->toDateString(),
                    $endDate->toDateString(),
                ])
                    ->where('is_local', true)
                    ->count();

                $values[] = $count;
            }
        } else {
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $dates[] = $date->format('M d');

                $count = Video::whereDate('created_at', $date->toDateString())
                    ->where('is_local', true)
                    ->count();

                $values[] = $count;
            }
        }

        return [
            'dates' => $dates,
            'values' => $values,
        ];
    }

    protected function getEngagementData(int $days = 30): array
    {
        $dates = [];
        $likes = [];
        $comments = [];
        $commentLikes = [];
        $replyLikes = [];
        $replies = [];
        $shares = [];

        $displayDays = min($days, 30);

        for ($i = $displayDays - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dates[] = $date->format('M d');

            $likesCount = VideoLike::whereDate('created_at', $date->toDateString())->count();
            $commentLikesCount = CommentLike::whereDate('created_at', $date->toDateString())->count();
            $commentsCount = Comment::whereDate('created_at', $date->toDateString())->count();
            $repliesCount = CommentReply::whereDate('created_at', $date->toDateString())->count();
            $replyLikesCount = CommentReplyLike::whereDate('created_at', $date->toDateString())->count();
            $sharesCount = VideoRepost::whereDate('created_at', $date->toDateString())->count();

            $likes[] = $likesCount;
            $comments[] = $commentsCount;
            $shares[] = $sharesCount;
            $replies[] = $repliesCount;
            $commentLikes[] = $commentLikesCount;
            $replyLikes[] = $replyLikesCount;
        }

        return [
            'dates' => $dates,
            'likes' => $likes,
            'comments' => $comments,
            'shares' => $shares,
            'replies' => $replies,
            'commentLikes' => $commentLikes,
            'replyLikes' => $replyLikes,
        ];
    }

    protected function getContentDistribution(): array
    {
        $totalVideos = Video::count();
        $localVideos = Video::where('is_local', true)->count();
        $remoteVideos = Video::where('is_local', false)->count();

        $withComments = Video::where('comments', '>', 0)->count();
        $withoutComments = $totalVideos - $withComments;

        return [
            ['name' => 'Local Videos', 'value' => $localVideos],
            ['name' => 'Federated Videos', 'value' => $remoteVideos],
            ['name' => 'With Comments', 'value' => $withComments],
            ['name' => 'Without Comments', 'value' => $withoutComments],
        ];
    }

    protected function getTopHashtags(): array
    {
        return Hashtag::where('can_search', true)
            ->where('is_banned', false)
            ->orderByDesc('count')
            ->limit(10)
            ->get(['id', 'name', 'count'])
            ->toArray();
    }

    protected function getFederationStats(): array
    {
        return [
            'total_instances' => Instance::count(),
            'remote_profiles' => Profile::where('local', false)->count(),
            'remote_videos' => Video::where('is_local', false)->count(),
            'remote_comments' => Comment::whereNotNull('ap_id')->count(),
            'remote_replies' => CommentReply::whereNotNull('ap_id')->count(),
            'remote_following' => Follower::where('following_is_local', false)->where('profile_is_local', true)->count(),
            'remote_followers' => Follower::where('following_is_local', true)->where('profile_is_local', false)->count(),
            'blocked_instances' => Instance::where('is_blocked', true)->count(),
        ];
    }

    protected function getModerationStats(int $days = 30): array
    {
        $periodStart = Carbon::now()->subDays($days);

        return [
            'pending_reports' => Report::where('admin_seen', false)->count(),
            'suspended_users' => Profile::where('is_suspended', true)->count(),
            'total_reports_30d' => Report::where('created_at', '>=', $periodStart)->count(),
        ];
    }

    protected function getRecentActivity(): array
    {
        $activity = [];

        $recentUsers = User::orderByDesc('created_at')
            ->limit(3)
            ->get(['id', 'username', 'created_at', 'profile_id']);

        foreach ($recentUsers as $user) {
            $activity[] = [
                'id' => 'user_'.$user->id,
                'icon' => 'new_user',
                'description' => "New user registered: @{$user->username}",
                'timestamp' => $user->created_at,
                'link' => "/admin/profiles/{$user->profile_id}",
            ];
        }

        $recentVideos = Video::where('is_local', true)
            ->with('profile')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        foreach ($recentVideos as $video) {
            $activity[] = [
                'id' => 'video_'.$video->id,
                'icon' => 'new_video',
                'description' => "New video uploaded by @{$video->profile->username}",
                'timestamp' => $video->created_at,
                'link' => "/admin/videos/{$video->id}",
            ];
        }

        $recentReports = Report::orderByDesc('created_at')
            ->limit(2)
            ->get();

        foreach ($recentReports as $report) {
            $activity[] = [
                'id' => 'report_'.$report->id,
                'icon' => 'new_report',
                'description' => 'New report',
                'timestamp' => $report->created_at,
                'link' => "/admin/reports/{$report->id}",
            ];
        }

        usort($activity, fn ($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return array_slice($activity, 0, 8);
    }

    protected function calculatePercentageChange($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
