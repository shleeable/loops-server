<?php

namespace App\Services;

class PushService
{
    protected PushTokenCacheService $tokenCache;

    protected PushRelayClientService $relayClient;

    public function __construct(
        PushTokenCacheService $tokenCache,
        PushRelayClientService $relayClient,
    ) {
        $this->tokenCache = $tokenCache;
        $this->relayClient = $relayClient;
    }

    public function newVideoLike(int $profileId, int $videoId, int $actorId): bool
    {
        $actor = $this->resolveUsername($actorId);

        return $this->send($profileId, "{$actor} liked your video", [
            'url' => '/private/video/'.$videoId,
        ]);
    }

    public function newVideoComment(int $profileId, int $videoId, int $actorId, int $commentId): bool
    {
        $actor = $this->resolveUsername($actorId);

        return $this->send($profileId, "{$actor} commented on your video", [
            'url' => '/private/video/'.$videoId,
        ]);
    }

    public function newVideoCommentReply(int $profileId, int $videoId, int $actorId, int $commentId): bool
    {
        $actor = $this->resolveUsername($actorId);

        return $this->send($profileId, "{$actor} replied to your comment", [
            'url' => '/private/video/'.$videoId,
        ]);
    }

    public function newFollow(int $profileId, int $actorId): bool
    {
        $actor = $this->resolveUsername($actorId);

        return $this->send($profileId, "{$actor} followed you", [
            'url' => '/private/profile/'.$actorId,
        ]);
    }

    public function newFollowRequest(int $profileId, int $actorId): bool
    {
        $actor = $this->resolveUsername($actorId);

        return $this->send($profileId, "{$actor} requested to follow you", [
            'url' => '/private/profile/'.$actorId,
        ]);
    }

    public function newMention(int $profileId, int $videoId, int $actorId): bool
    {
        $actor = $this->resolveUsername($actorId);

        return $this->send($profileId, "{$actor} mentioned you in a video", [
            'url' => '/private/video/'.$videoId,
        ]);
    }

    public function newCommentMention(int $profileId, int $videoId, int $actorId, int $commentId): bool
    {
        $actor = $this->resolveUsername($actorId);

        return $this->send($profileId, "{$actor} mentioned you in a comment", [
            'url' => '/private/video/'.$videoId,
        ]);
    }

    public function newDirectMessage(int $profileId, int $actorId): bool
    {
        $actor = $this->resolveUsername($actorId);

        return $this->send($profileId, "{$actor} sent you a message", [
            // todo
        ]);
    }

    public function newRepost(int $profileId, int $videoId, int $actorId): bool
    {
        $actor = $this->resolveUsername($actorId);

        return $this->send($profileId, "{$actor} reposted your video", [
            // todo
        ]);
    }

    public function newDuet(int $profileId, int $videoId, int $actorId, int $duetVideoId): bool
    {
        $actor = $this->resolveUsername($actorId);

        return $this->send($profileId, "{$actor} made a duet with your video", [
            'url' => '/private/video/'.$duetVideoId,
        ]);
    }

    public function videoProcessed(int $profileId, int $videoId): bool
    {
        return $this->send($profileId, 'Your video has been published!', [
            'url' => '/private/video/'.$videoId,
        ]);
    }

    protected function send(int $profileId, string $message, array $data = []): bool
    {
        $token = $this->tokenCache->get((string) $profileId);

        if (! $token) {
            return false;
        }

        $data['profile_id'] = (string) $profileId;

        return $this->relayClient->notify($token, $message, $data);
    }

    protected function resolveUsername(int $profileId): string
    {
        $account = AccountService::compact($profileId, true);

        return data_get($account, 'username', 'Someone');
    }
}
