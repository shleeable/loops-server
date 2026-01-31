<?php

namespace App\Console\Commands;

use App\Jobs\Video\VideoOptimizeJob;
use App\Jobs\Video\VideoProcessingCompleteJob;
use App\Jobs\Video\VideoThumbnailJob;
use App\Models\Video;
use App\Services\ConfigService;
use Illuminate\Console\Command;

class RetryFailedVideos extends Command
{
    protected $signature = 'videos:retry-failed {--id= : Specific video ID to retry}';

    protected $description = 'Retry processing for failed videos';

    public function handle()
    {
        $query = Video::where('is_local', true)->where('processing_status', 'failed');

        if ($this->option('id')) {
            $query->where('id', $this->option('id'));
        }

        $videos = $query->get();

        if ($videos->isEmpty()) {
            $this->info('No failed videos found.');

            return;
        }

        $this->info("Found {$videos->count()} failed video(s). Retrying...");

        $config = app(ConfigService::class);

        foreach ($videos as $video) {
            $video->processing_status = 'processing';
            $video->processing_error = null;
            $video->processing_failed_at = null;
            $video->save();

            VideoThumbnailJob::withChain([
                new VideoOptimizeJob($video),
                new VideoProcessingCompleteJob($video),
            ])->dispatch($video);

            $this->info("Retrying video ID: {$video->id}");
        }

        $this->info('Retry jobs dispatched.');
    }
}
