<?php

namespace App\Console\Commands;

use App\Jobs\Video\VideoOptimizeJob;
use App\Jobs\Video\VideoThumbnailJob;
use App\Models\Video;
use App\Services\ConfigService;
use App\Services\FederationDispatcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

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

            Bus::batch([
                [new VideoThumbnailJob($video)],
                [new VideoOptimizeJob($video)],
            ])
                ->then(function () use ($video, $config) {
                    $video->processing_status = 'completed';
                    $video->save();

                    if ($config->federation() && $video->is_local && ! $video->federated_at) {
                        app(FederationDispatcher::class)->dispatchVideoCreation($video);

                        $video->federated_at = now();
                        $video->save();

                        $this->info("Federation dispatched for video ID: {$video->id}");
                    } elseif ($video->federated_at) {
                        $this->info("Video ID: {$video->id} was already federated, skipping.");
                    }
                })
                ->catch(function ($batch, $e) use ($video) {
                    $video->processing_status = 'failed';
                    $video->processing_error = $e->getMessage();
                    $video->processing_failed_at = now();
                    $video->save();

                    $this->error("Video ID {$video->id} failed again: {$e->getMessage()}");
                })
                ->allowFailures()
                ->dispatch();

            $this->info("Retrying video ID: {$video->id}");
        }

        $this->info('Retry jobs dispatched.');
    }
}
