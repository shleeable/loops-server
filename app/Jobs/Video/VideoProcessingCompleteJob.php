<?php

namespace App\Jobs\Video;

use App\Services\ConfigService;
use App\Services\FederationDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class VideoProcessingCompleteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $video;

    public $timeout = 60;

    public $tries = 3;

    public $deleteWhenMissingModels = true;

    public function __construct($video)
    {
        $this->video = $video->withoutRelations();
    }

    public function handle(): void
    {
        $video = $this->video->fresh();

        if (! $video) {
            Log::warning('Video not found for completion job', ['video_id' => $this->video->id]);

            return;
        }

        try {
            $video->processing_status = 'completed';
            $video->save();

            $config = app(ConfigService::class);

            if ($config->federation() && ! $video->federated_at) {
                app(FederationDispatcher::class)->dispatchVideoCreation($video);

                $video->federated_at = now();
                $video->save();
            }
        } catch (\Exception $e) {
            Log::error('Video completion processing failed', [
                'video_id' => $video->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Video completion job permanently failed', [
            'video_id' => $this->video->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
