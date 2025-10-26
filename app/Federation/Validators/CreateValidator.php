<?php

namespace App\Federation\Validators;

use App\Models\Comment;
use App\Models\CommentReply;
use App\Models\Video;
use App\Services\ActivityPubService;
use App\Services\HashidService;
use App\Services\SanitizeService;
use Illuminate\Support\Facades\Log;

class CreateValidator extends BaseValidator
{
    public function validate(array $activity): bool
    {
        if (! $this->hasRequiredFields($activity, ['type', 'actor', 'object'])) {
            return false;
        }

        if ($activity['type'] !== 'Create') {
            return false;
        }

        if (! is_array($activity['object'])) {
            return false;
        }

        if (! isset($activity['object']['type'], $activity['object']['inReplyTo']) || ! in_array($activity['object']['type'], ['Note'])) {
            return false;
        }

        if (! app(SanitizeService::class)->url($activity['actor'], true)) {
            return false;
        }

        if (empty($activity['object']['inReplyTo'])) {
            return false;
        }

        return $this->validateReplyChain($activity['object']['inReplyTo']);
    }

    /**
     * Walk the reply chain to ensure it eventually leads to a local Video
     */
    private function validateReplyChain(string $inReplyToUrl, int $depth = 0): bool
    {
        if ($depth > 10) {
            if (config('logging.dev_log')) {
                Log::warning('Reply chain depth exceeded', ['url' => $inReplyToUrl, 'depth' => $depth]);
            }

            return false;
        }

        if (! app(SanitizeService::class)->url($inReplyToUrl, true)) {
            if (config('logging.dev_log')) {
                Log::warning('Invalid inReplyToUrl url, may be banned or inaccessible', ['url' => $inReplyToUrl, 'depth' => $depth]);
            }

            return false;
        }

        $replyUrl = parse_url($inReplyToUrl);
        $baseDomain = $this->localDomain();
        $isLocal = $this->isLocalObject($inReplyToUrl);

        if ($isLocal) {
            $videoHashIdMatch = app(SanitizeService::class)->matchUrlTemplate(
                url: $inReplyToUrl,
                templates: '/v/{hashId}',
                useAppHost: true,
                constraints: ['hashId' => '[0-9a-zA-Z_-]{1,11}']
            );

            if ($videoHashIdMatch && isset($videoHashIdMatch['hashId'])) {
                $decodedId = HashidService::safeDecode($videoHashIdMatch['hashId']);
                if ($decodedId !== null) {
                    return Video::where('id', $decodedId)->exists();
                }
            }

            $videoMatch = app(SanitizeService::class)->matchUrlTemplate(
                url: $inReplyToUrl,
                templates: '/ap/users/{profileId}/video/{videoId}',
                useAppHost: true,
                constraints: ['profileId' => '\d+', 'videoId' => '\d+']
            );

            if ($videoMatch && isset($videoMatch['videoId'])) {
                return Video::where('id', $videoMatch['videoId'])->exists();
            }

            $commentMatch = app(SanitizeService::class)->matchUrlTemplate(
                url: $inReplyToUrl,
                templates: '/ap/users/{profileId}/comment/{commentId}',
                useAppHost: true,
                constraints: ['profileId' => '\d+', 'commentId' => '\d+']
            );

            if ($commentMatch && isset($commentMatch['commentId'])) {
                return Comment::where('id', $commentMatch['commentId'])->exists();
            }

            $replyMatch = app(SanitizeService::class)->matchUrlTemplate(
                url: $inReplyToUrl,
                templates: '/ap/users/{profileId}/reply/{replyId}',
                useAppHost: true,
                constraints: ['profileId' => '\d+', 'replyId' => '\d+']
            );

            if ($replyMatch && isset($replyMatch['replyId'])) {
                return CommentReply::where('id', $replyMatch['replyId'])->exists();
            }

            return false;
        }

        $comment = Comment::where('ap_id', $inReplyToUrl)->first();
        if ($comment) {
            return true;
        }

        $commentReply = CommentReply::where('ap_id', $inReplyToUrl)->first();
        if ($commentReply) {
            return true;
        }

        try {
            $response = app(ActivityPubService::class)->get($inReplyToUrl);

            if (! $response) {
                if (config('logging.dev_log')) {
                    Log::warning('Failed to fetch remote object', ['url' => $inReplyToUrl]);
                }

                return false;
            }

            $remoteObject = $response;

            if (isset($remoteObject['inReplyTo']) && ! empty($remoteObject['inReplyTo'])) {
                return $this->validateReplyChain($remoteObject['inReplyTo'], $depth + 1);
            }

            return false;

        } catch (\Exception $e) {
            if (config('logging.dev_log')) {
                Log::error('Error fetching remote object for reply validation', [
                    'url' => $inReplyToUrl,
                    'error' => $e->getMessage(),
                ]);
            }

            return false;
        }
    }
}
