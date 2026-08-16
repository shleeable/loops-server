<?php

namespace App\Jobs\Video;

use App\Models\Video;
use App\Services\VideoScheduleService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\SkipIfBatchCancelled;

class PublishScheduledVideoJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $timeout = 60;

    public $deleteWhenMissingModels = true;

    public function __construct(public Video $video)
    {
        $this->onQueue('default');
    }

    public function uniqueId(): string
    {
        return 'publish-scheduled:'.$this->video->id;
    }

    public function uniqueFor(): int
    {
        return 900;
    }

    public function backoff(): array
    {
        return [10, 60, 300];
    }

    public function middleware(): array
    {
        return [new SkipIfBatchCancelled];
    }

    public function handle(VideoScheduleService $service): void
    {
        $video = $this->video->fresh();

        if (! $video) {
            return;
        }

        $service->publish($video);
    }

    public function failed(\Throwable $e): void
    {
        $video = $this->video->fresh();

        if (! $video || $video->isLive()) {
            return;
        }

        app(VideoScheduleService::class)->markFailed($video, $e->getMessage());
    }
}
