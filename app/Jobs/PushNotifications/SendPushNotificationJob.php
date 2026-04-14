<?php

namespace App\Jobs\PushNotifications;

use App\Services\PushService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPushNotificationJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public int $uniqueFor = 300;

    public function __construct(
        public string $method,
        public array $params,
    ) {
        $this->onQueue('push');
    }

    public function uniqueId(): string
    {
        return $this->method.':'.implode(':', $this->params);
    }

    public function handle(PushService $service): void
    {
        if (! method_exists($service, $this->method)) {
            $this->fail(new \InvalidArgumentException("Unknown push method: {$this->method}"));

            return;
        }

        $service->{$this->method}(...$this->params);
    }

    public static function dispatch_newVideoLike(int $profileId, int $videoId, int $actorId): void
    {
        static::dispatch('newVideoLike', [$profileId, $videoId, $actorId]);
    }

    public static function dispatch_newVideoComment(int $profileId, int $videoId, int $actorId, int $commentId): void
    {
        static::dispatch('newVideoComment', [$profileId, $videoId, $actorId, $commentId]);
    }

    public static function dispatch_newVideoCommentReply(int $profileId, int $videoId, int $actorId, int $commentId): void
    {
        static::dispatch('newVideoCommentReply', [$profileId, $videoId, $actorId, $commentId]);
    }

    public static function dispatch_newFollow(int $profileId, int $actorId): void
    {
        static::dispatch('newFollow', [$profileId, $actorId]);
    }

    public static function dispatch_newFollowRequest(int $profileId, int $actorId): void
    {
        static::dispatch('newFollowRequest', [$profileId, $actorId]);
    }

    public static function dispatch_newMention(int $profileId, int $videoId, int $actorId): void
    {
        static::dispatch('newMention', [$profileId, $videoId, $actorId]);
    }

    public static function dispatch_newCommentMention(int $profileId, int $videoId, int $actorId, int $commentId): void
    {
        static::dispatch('newCommentMention', [$profileId, $videoId, $actorId, $commentId]);
    }

    public static function dispatch_newDirectMessage(int $profileId, int $actorId): void
    {
        static::dispatch('newDirectMessage', [$profileId, $actorId]);
    }

    public static function dispatch_newRepost(int $profileId, int $videoId, int $actorId): void
    {
        static::dispatch('newRepost', [$profileId, $videoId, $actorId]);
    }

    public static function dispatch_newDuet(int $profileId, int $videoId, int $actorId, int $duetVideoId): void
    {
        static::dispatch('newDuet', [$profileId, $videoId, $actorId, $duetVideoId]);
    }

    public static function dispatch_videoProcessed(int $profileId, int $videoId): void
    {
        static::dispatch('videoProcessed', [$profileId, $videoId]);
    }

    public static function dispatch_videoFlagged(int $profileId, int $videoId): void
    {
        static::dispatch('videoFlagged', [$profileId, $videoId]);
    }
}
