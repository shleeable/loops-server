<?php

namespace App\Federation\Validators;

use App\Models\Comment;
use App\Models\CommentReply;
use App\Models\Profile;
use App\Models\Video;
use App\Services\SanitizeService;
use Illuminate\Support\Facades\Log;

class FlagValidator extends BaseValidator
{
    public function validate(array $activity): bool
    {
        if (! $this->hasRequiredFields($activity, ['type', 'actor', 'object'])) {
            return false;
        }

        if ($activity['type'] !== 'Flag') {
            return false;
        }

        if (! is_string($activity['actor'])) {
            return false;
        }

        if (! app(SanitizeService::class)->url($activity['actor'], true)) {
            return false;
        }

        $objects = is_array($activity['object']) ? $activity['object'] : [$activity['object']];

        if (empty($objects)) {
            return false;
        }

        foreach ($objects as $object) {
            if (! $this->validateReportableObject($object)) {
                return false;
            }
        }

        if (isset($activity['to'])) {
            if (! $this->isLocalProfile($activity['to'])) {
                if (config('logging.dev_log')) {
                    Log::warning('Flag activity rejected - target is not local', [
                        'actor' => $activity['actor'],
                        'to' => $activity['to'],
                    ]);
                }

                return false;
            }
        }

        return true;
    }

    /**
     * Validate that the object being reported is valid and involves local content
     */
    private function validateReportableObject($object): bool
    {
        if (! is_string($object)) {
            return false;
        }

        if (! app(SanitizeService::class)->url($object)) {
            return false;
        }

        // Check if this is a local profile, video, comment, or comment reply
        return $this->isLocalProfile($object) ||
               $this->isLocalVideo($object) ||
               $this->isLocalComment($object) ||
               $this->isLocalCommentReply($object);
    }

    /**
     * Check if the given URL represents a local profile
     */
    private function isLocalProfile(string $url): bool
    {
        $isLocal = $this->isLocalObject($url);

        if (! $isLocal) {
            return false;
        }

        $profileMatch = app(SanitizeService::class)->matchUrlTemplate(
            url: $url,
            templates: [
                '/ap/users/{profileId}',
            ],
            useAppHost: true,
            constraints: ['profileId' => '\d+']
        );

        if ($profileMatch) {
            if (isset($profileMatch['profileId'])) {
                return Profile::where('id', $profileMatch['profileId'])->whereStatus(1)->whereLocal(true)->exists();
            }
        }

        return false;
    }

    /**
     * Check if the given URL represents a local video
     */
    private function isLocalVideo(string $url): bool
    {
        $isLocal = $this->isLocalObject($url);

        if (! $isLocal) {
            return false;
        }

        $videoMatch = app(SanitizeService::class)->matchUrlTemplate(
            url: $url,
            templates: [
                '/ap/users/{userId}/video/{videoId}',
            ],
            useAppHost: true,
            constraints: ['userId' => '\d+', 'videoId' => '\d+']
        );

        if ($videoMatch && isset($videoMatch['userId'], $videoMatch['videoId'])) {
            return Video::whereProfileId($videoMatch['userId'])->whereKey($videoMatch['videoId'])->exists();
        }

        return false;
    }

    /**
     * Check if the given URL represents a local comment
     */
    private function isLocalComment(string $url): bool
    {
        $isLocal = $this->isLocalObject($url);

        if (! $isLocal) {
            return false;
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

        return false;
    }

    /**
     * Check if the given URL represents a local comment reply
     */
    private function isLocalCommentReply(string $url): bool
    {
        $isLocal = $this->isLocalObject($url);

        if (! $isLocal) {
            return false;
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
    }
}
