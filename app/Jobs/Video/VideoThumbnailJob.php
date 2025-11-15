<?php

namespace App\Jobs\Video;

use App\Services\VideoService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class VideoThumbnailJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $video;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    public $tries = 3;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 3;

    public $deleteWhenMissingModels = true;

    /**
     * Get the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [(new WithoutOverlapping('video-thumb:'.$this->video->id))->expireAfter(180)];
    }

    /**
     * Create a new job instance.
     */
    public function __construct($video)
    {
        $this->video = $video;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch()->cancelled()) {
            return;
        }

        $video = $this->video;

        if (str_starts_with($video->vid, 'https://')) {
            return;
        }

        $ext = pathinfo($video->vid, PATHINFO_EXTENSION);
        $thumb = str_replace('.'.$ext, '.jpg', $video->vid);

        $indexSec = 0;
        if ($video->size_kb && $video->size_kb > 8000) {
            $indexSec = 2;
        }

        $media = FFMpeg::fromDisk('s3')
            ->open($video->vid)
            ->getFrameFromSeconds($indexSec)
            ->export()
            ->toDisk('s3')
            ->withVisibility('public')
            ->save($thumb);
        $media->cleanupTemporaryFiles();

        $video->has_thumb = true;
        $video->save();

        VideoService::deleteMediaData($video->id);
    }
}
