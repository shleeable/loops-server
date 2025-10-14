<?php

namespace App\Federation\Validators;

use App\Models\Comment;
use App\Models\CommentReply;
use App\Models\Profile;
use App\Models\Video;
use App\Services\SanitizeService;
use Illuminate\Support\Facades\Log;

class UndoValidator extends BaseValidator
{
    public function validate(array $activity): bool
    {
        if (! $this->hasRequiredFields($activity, ['type', 'actor', 'object'])) {
            return false;
        }

        if ($activity['type'] !== 'Undo') {
            return false;
        }

        if (! is_string($activity['actor'])) {
            return false;
        }

        if (! is_array($activity['object'])) {
            return false;
        }

        if (! app(SanitizeService::class)->url($activity['actor'], true)) {
            return false;
        }

        $object = $activity['object'];

        if (! isset($object['type'], $object['object']) || ! is_string($object['type']) || ! is_string($object['object'])) {
            return false;
        }

        if (isset($object['actor']) && $object['actor'] !== $activity['actor']) {
            if (config('logging.dev_log')) {
                Log::warning('Undo activity rejected - actor mismatch', [
                    'undo_actor' => $activity['actor'],
                    'original_actor' => $object['actor'],
                ]);
            }

            return false;
        }

        if (! app(SanitizeService::class)->url($object['object'], true)) {
            return false;
        }

        return match ($object['type']) {
            'Follow' => $this->validateUndoFollow($activity, $object),
            'Like' => $this->validateUndoLike($activity, $object),
            'Announce' => $this->validateUndoAnnounce($activity, $object),
            default => $this->validateGenericUndo($activity, $object)
        };
    }

    private function validateUndoFollow(array $activity, array $followObject): bool
    {
        if (! isset($followObject['object']) || ! is_string($followObject['object'])) {
            return false;
        }

        if (! $this->isLocalProfile($followObject['object'])) {
            if (config('logging.dev_log')) {
                Log::warning('Undo Follow activity rejected - target is not a local profile', [
                    'actor' => $activity['actor'],
                    'object' => $followObject['object'],
                ]);
            }

            return false;
        }

        return true;
    }

    private function validateUndoLike(array $activity, array $likeObject): bool
    {
        if (! isset($likeObject['object']) || ! is_string($likeObject['object'])) {
            return false;
        }

        if (! $this->isLocalStatus($likeObject['object'])) {
            if (config('logging.dev_log')) {
                Log::warning('Undo Like activity rejected - target is not a local status', [
                    'actor' => $activity['actor'],
                    'object' => $likeObject['object'],
                ]);
            }

            return false;
        }

        return true;
    }

    private function validateUndoAnnounce(array $activity, array $announceObject): bool
    {
        if (! isset($announceObject['object']) || ! is_string($announceObject['object'])) {
            return false;
        }

        if (! $this->isLocalStatus($announceObject['object'])) {
            if (config('logging.dev_log')) {
                Log::warning('Undo Announce activity rejected - target is not a local status', [
                    'actor' => $activity['actor'],
                    'object' => $announceObject['object'],
                ]);
            }

            return false;
        }

        return true;
    }

    private function validateGenericUndo(array $activity, array $object): bool
    {
        if (config('logging.dev_log')) {
            Log::info('Generic Undo activity validation - unknown type', [
                'actor' => $activity['actor'],
                'object_type' => $object['type'],
            ]);
        }

        return true;
    }

    /**
     * Check if the given URL represents a local profile
     */
    private function isLocalProfile(string $url): bool
    {
        $isLocal = $this->isLocalObject($url);

        if (! $isLocal) {
            return Profile::where('uri', $url)->exists();
        }

        $profileMatch = app(SanitizeService::class)->matchUrlTemplate(
            url: $url,
            templates: [
                '/ap/users/{profileId}',
                '/@{username}',
                '/users/{username}',
            ],
            useAppHost: true,
            constraints: ['profileId' => '\d+', 'username' => '[a-zA-Z0-9_.-]+']
        );

        if ($profileMatch) {
            if (isset($profileMatch['profileId'])) {
                return Profile::where('id', $profileMatch['profileId'])->whereLocal(true)->exists();
            }

            if (isset($profileMatch['username'])) {
                return Profile::where('username', $profileMatch['username'])->whereLocal(true)->exists();
            }
        }

        return false;
    }

    /**
     * Check if the given URL represents a local status
     */
    private function isLocalStatus(string $url): bool
    {
        $isLocal = $this->isLocalObject($url);

        if (! $isLocal) {
            return $this->isRemoteStatus($url);
        }

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
                '/ap/users/{userId}/comment/{commentId}',
            ],
            useAppHost: true,
            constraints: ['userId' => '\d+', 'commentId' => '\d+']
        );

        if ($commentMatch && isset($commentMatch['userId'], $commentMatch['commentId'])) {
            return Comment::whereProfileId($commentMatch['userId'])->whereKey($commentMatch['commentId'])->exists();
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

    private function isRemoteStatus(string $url): bool
    {
        $comment = Comment::where('ap_id', $url)->exists();
        if ($comment) {
            return true;
        }

        $commentReply = CommentReply::where('ap_id', $url)->exists();
        if ($commentReply) {
            return true;
        }

        $video = Video::whereUri($url)->exists();
        if ($video) {
            return true;
        }

        return false;
    }
}
