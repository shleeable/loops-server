<?php

namespace App\Federation\Validators;

use App\Models\Comment;
use App\Models\CommentReply;
use App\Models\Video;
use App\Services\SanitizeService;
use Illuminate\Support\Facades\Log;

class AnnounceValidator extends BaseValidator
{
    public function validate(array $activity): bool
    {
        if (! $this->hasRequiredFields($activity, ['type', 'actor', 'object'])) {
            return false;
        }

        if ($activity['type'] !== 'Announce') {
            return false;
        }

        if (! is_string($activity['actor'])) {
            return false;
        }

        if (! is_string($activity['object'])) {
            return false;
        }

        if (! app(SanitizeService::class)->url($activity['actor'], true)) {
            return false;
        }

        if (! $this->isLocalStatus($activity['object'])) {
            if (config('logging.dev_log')) {
                Log::warning('Announce activity rejected - target is not a local status', [
                    'actor' => $activity['actor'],
                    'object' => $activity['object'],
                ]);
            }

            return false;
        }

        return true;
    }

    /**
     * Check if the given URL represents a local status
     */
    private function isLocalStatus(string $url): bool
    {
        $isLocal = $this->isLocalObject($url);

        if ($isLocal) {
            $statusMatch = app(SanitizeService::class)->matchUrlTemplate(
                url: $url,
                templates: [
                    '/ap/users/{userId}/video/{videoId}',
                ],
                useAppHost: true,
                constraints: ['userId' => '\d+', 'videoId' => '\d+']
            );

            if ($statusMatch && isset($statusMatch['userId'], $statusMatch['videoId'])) {
                return Video::whereProfileId($statusMatch['userId'])->whereKey($statusMatch['videoId'])->exists();
            }

            $commentMatch = app(SanitizeService::class)->matchUrlTemplate(
                url: $url,
                templates: [
                    '/ap/users/{userId}/comment/{replyId}',
                ],
                useAppHost: true,
                constraints: ['userId' => '\d+', 'replyId' => '\d+']
            );

            if ($commentMatch && isset($commentMatch['userId'], $commentMatch['replyId'])) {
                return Comment::whereProfileId($commentMatch['userId'])->whereKey($commentMatch['replyId'])->exists();
            }

            $commentReplyMatch = app(SanitizeService::class)->matchUrlTemplate(
                url: $url,
                templates: [
                    '/ap/users/{userId}/reply/{commentReplyId}',
                ],
                useAppHost: true,
                constraints: ['userId' => '\d+', 'commentReplyId' => '\d+']
            );

            if ($commentReplyMatch && isset($commentReplyMatch['userId'], $commentReplyMatch['commentReplyId'])) {
                return CommentReply::whereProfileId($commentReplyMatch['userId'])->whereKey($commentReplyMatch['commentReplyId'])->exists();
            }

            return false;
        } else {
            $commentMatch = Comment::where('ap_id', $url)->exists();
            if ($commentMatch) {
                return true;
            }

            $commentReplyMatch = CommentReply::where('ap_id', $url)->exists();
            if ($commentReplyMatch) {
                return true;
            }

            $videoMatch = Video::where('uri', $url)->exists();
            if ($videoMatch) {
                return true;
            }
        }

        return false;
    }
}
