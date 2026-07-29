<?php

namespace App\Console\Commands;

use App\Models\Comment;
use App\Models\CommentReply;
use App\Models\Follower;
use App\Models\Instance;
use App\Models\Profile;
use App\Models\Report;
use App\Models\Video;
use App\Services\InstanceService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class InstanceStatsCollectorCommand extends Command
{
    protected $signature = 'app:instance-stats-collector-command
        {limit=500 : Maximum number of instances to process}
        {subDays=3 : Recollect stats older than this many days}
        {--force : Skip the collection cutoff and process instances regardless of age or blocked status}';

    protected $description = 'Collect statistics for instances that need updating';

    public function handle(): int
    {
        $limit = (int) $this->argument('limit');
        $subDays = (int) $this->argument('subDays');
        $force = (bool) $this->option('force');

        if ($limit < 1) {
            $this->error('The limit must be a positive integer.');

            return self::FAILURE;
        }

        if (! $force && $subDays < 1) {
            $this->error('The subDays value must be a positive integer.');

            return self::FAILURE;
        }

        $now = now();
        $cutoff = $now->copy()->subDays($subDays);

        $instances = Instance::query()
            ->when(! $force, function (Builder $query): void {
                $query->where('is_blocked', false);
            })
            ->when(! $force, function (Builder $query) use ($cutoff): void {
                $query->where(function (Builder $query) use ($cutoff): void {
                    $query
                        ->whereNull('stats_last_collected_at')
                        ->orWhere('stats_last_collected_at', '<', $cutoff);
                });
            })
            ->orderBy('stats_last_collected_at')
            ->orderBy('id')
            ->limit($limit)
            ->get(['id', 'domain']);

        if ($instances->isEmpty()) {
            $this->info('Collected stats for 0 instances');

            return self::SUCCESS;
        }

        $domains = $instances->pluck('domain');

        $users = Profile::query()
            ->whereIn('domain', $domains)
            ->groupBy('domain')
            ->selectRaw('domain, COUNT(*) as aggregate')
            ->pluck('aggregate', 'domain');

        $videos = $this->countsByDomain(
            Video::query(),
            'videos.profile_id',
            $domains,
        );

        $comments = $this->countsByDomain(
            Comment::query(),
            'comments.profile_id',
            $domains,
        );

        $replies = $this->countsByDomain(
            CommentReply::query(),
            'comment_replies.profile_id',
            $domains,
        );

        $followers = $this->countsByDomain(
            Follower::query(),
            'followers.following_id',
            $domains,
        );

        $following = $this->countsByDomain(
            Follower::query(),
            'followers.profile_id',
            $domains,
        );

        $reports = $this->countsByDomain(
            Report::query(),
            'reports.reported_profile_id',
            $domains,
        );

        $rows = $instances
            ->map(fn (Instance $instance): array => [
                'id' => $instance->id,
                'domain' => $instance->domain,
                'user_count' => $users[$instance->domain] ?? 0,
                'video_count' => $videos[$instance->domain] ?? 0,
                'comment_count' => $comments[$instance->domain] ?? 0,
                'reply_count' => $replies[$instance->domain] ?? 0,
                'follower_count' => $followers[$instance->domain] ?? 0,
                'following_count' => $following[$instance->domain] ?? 0,
                'report_count' => $reports[$instance->domain] ?? 0,
                'stats_last_collected_at' => $now,
            ])
            ->all();

        Instance::upsert($rows, ['id'], [
            'user_count',
            'video_count',
            'comment_count',
            'reply_count',
            'follower_count',
            'following_count',
            'report_count',
            'stats_last_collected_at',
        ]);

        app(InstanceService::class)->flushStats();

        $this->info('Collected stats for '.count($rows).' instances');

        Cache::forget('federation:top-instance-inboxes:50');

        return self::SUCCESS;
    }

    protected function countsByDomain(
        Builder $query,
        string $foreignKey,
        Collection $domains,
    ): Collection {
        return $query
            ->join('profiles', $foreignKey, '=', 'profiles.id')
            ->whereIn('profiles.domain', $domains)
            ->groupBy('profiles.domain')
            ->selectRaw('profiles.domain as domain, COUNT(*) as aggregate')
            ->pluck('aggregate', 'domain');
    }
}
