<?php

namespace App\Console\Commands;

use App\Models\Comment;
use App\Models\CommentReply;
use App\Models\Follower;
use App\Models\Instance;
use App\Models\Profile;
use App\Models\Report;
use App\Models\Video;
use Illuminate\Console\Command;

class InstanceStatsCollectorCommand extends Command
{
    protected $signature = 'app:instance-stats-collector-command';

    protected $description = 'Collect statistics for instances that need updating';

    public function handle()
    {
        $count = 0;
        $now = now();

        $instances = Instance::where(function ($query) {
            $query->whereNull('stats_last_collected_at')
                ->orWhere('stats_last_collected_at', '<', now()->subDay());
        })
            ->where('is_blocked', false)
            ->limit(250)
            ->get();

        foreach ($instances as $instance) {
            $instance->user_count = Profile::whereDomain($instance->domain)->count();
            $instance->video_count = Video::join('profiles', 'videos.profile_id', '=', 'profiles.id')
                ->where('profiles.domain', $instance->domain)
                ->count();
            $instance->comment_count = Comment::join('profiles', 'comments.profile_id', '=', 'profiles.id')
                ->where('profiles.domain', $instance->domain)
                ->count();
            $instance->reply_count = CommentReply::join('profiles', 'comment_replies.profile_id', '=', 'profiles.id')
                ->where('profiles.domain', $instance->domain)
                ->count();
            $instance->follower_count = Follower::join('profiles', 'followers.following_id', '=', 'profiles.id')
                ->where('profiles.domain', $instance->domain)
                ->count();
            $instance->following_count = Follower::join('profiles', 'followers.profile_id', '=', 'profiles.id')
                ->where('profiles.domain', $instance->domain)
                ->count();
            $instance->report_count = Report::join('profiles', 'reports.reported_profile_id', '=', 'profiles.id')
                ->where('profiles.domain', $instance->domain)
                ->count();
            $instance->stats_last_collected_at = $now;
            $instance->save();

            $count++;
        }

        $this->info("Collected stats for {$count} instances");
    }
}
