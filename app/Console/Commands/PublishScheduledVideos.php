<?php

namespace App\Console\Commands;

use App\Jobs\Video\PublishScheduledVideoJob;
use App\Models\Video;
use App\Services\VideoScheduleService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('loops:publish-scheduled {--limit=500}')]
#[Description('Publish videos whose scheduled time has arrived')]
class PublishScheduledVideos extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(VideoScheduleService $service): int
    {
        $released = $service->releaseStaleClaims();

        if ($released) {
            $this->warn("Released {$released} stale publish claims");
        }

        $dispatched = 0;
        $limit = (int) $this->option('limit');

        Video::query()
            ->where('publish_state', Video::PUBLISH_STATE_SCHEDULED)
            ->where('status', '!=', VideoScheduleService::STATUS_LIVE)
            ->whereNull('ap_published_at')
            ->whereNull('publishing_at')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->chunkById(100, function ($videos) use ($service, &$dispatched) {
                foreach ($videos as $video) {
                    if (! $service->claim($video->id)) {
                        continue;
                    }

                    PublishScheduledVideoJob::dispatch($video);
                    $dispatched++;
                }
            });

        $this->info("Dispatched {$dispatched} scheduled videos");

        return self::SUCCESS;
    }
}
