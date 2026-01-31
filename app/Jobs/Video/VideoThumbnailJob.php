<?php

namespace App\Jobs\Video;

use App\Services\VideoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class VideoThumbnailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $video;

    public $timeout = 120;

    public $tries = 3;

    public $maxExceptions = 3;

    public $deleteWhenMissingModels = true;

    public $backoff = [10, 30, 60];

    public function __construct($video)
    {
        $this->video = $video->withoutRelations();
        $this->onQueue('video-processing');
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping('video-thumb:'.$this->video->id))->expireAfter(180)];
    }

    public function handle(): void
    {
        $video = $this->video->fresh();

        if (! $video) {
            Log::warning('Video not found for thumbnail job', ['video_id' => $this->video->id]);

            return;
        }

        if (str_starts_with($video->vid, 'https://')) {
            return;
        }

        try {
            if (! Storage::disk('s3')->exists($video->vid)) {
                throw new \Exception('Video file not found on S3: '.$video->vid);
            }

            $ext = pathinfo($video->vid, PATHINFO_EXTENSION);
            $thumb = str_replace('.'.$ext, '.jpg', $video->vid);

            if (Storage::disk('s3')->exists($thumb)) {
                $video->has_thumb = true;
                $video->save();
                VideoService::deleteMediaData($video->id);

                return;
            }

            $indexSec = 0;

            $media = FFMpeg::fromDisk('s3')
                ->open($video->vid)
                ->getFrameFromSeconds($indexSec)
                ->export()
                ->toDisk('s3')
                ->withVisibility('public')
                ->save($thumb);

            $media->cleanupTemporaryFiles();

            // @phpstan-ignore-next-line
            if (! Storage::disk('s3')->exists($thumb)) {
                throw new \Exception('Thumbnail was not created on S3');
            }

            // @phpstan-ignore-next-line
            $video->has_thumb = true;
            $video->save();

            VideoService::deleteMediaData($video->id);
        } catch (\Exception $e) {
            Log::error('Video thumbnail generation failed', [
                'video_id' => $video->id,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($this->attempts() >= $this->tries) {
                $video->processing_error = 'Thumbnail generation failed: '.$e->getMessage();
                $video->processing_status = 'failed';
                $video->processing_failed_at = now();
                $video->save();
            }

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $video = $this->video->fresh();

        if ($video) {
            $video->processing_status = 'failed';
            $video->processing_error = 'Thumbnail: '.$exception->getMessage();
            $video->processing_failed_at = now();
            $video->save();
        }

        Log::error('Video thumbnail job permanently failed', [
            'video_id' => $this->video->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
