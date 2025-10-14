<?php

namespace App\Federation\Validators;

use App\Models\Comment;
use App\Models\CommentReply;
use App\Models\Profile;
use App\Models\Video;
use App\Services\SanitizeService;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Facades\Log;

class UpdateValidator extends BaseValidator
{
    public function validate(array $activity): bool
    {
        if (! $this->hasRequiredFields($activity, ['type', 'actor', 'object'])) {
            return false;
        }

        if ($activity['type'] !== 'Update') {
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

        if (! isset($object['type']) || ! is_string($object['type'])) {
            return false;
        }

        if (! isset($object['id']) || ! is_string($object['id'])) {
            return false;
        }

        if (! app(SanitizeService::class)->url($object['id'])) {
            return false;
        }

        return match ($object['type']) {
            'Note', 'Article', 'Video', 'Image' => $this->validateStatusUpdate($activity, $object),
            'Person' => $this->validateProfileUpdate($activity, $object),
            default => $this->validateGenericUpdate($activity, $object)
        };
    }

    private function validateStatusUpdate(array $activity, array $object): bool
    {
        if (! $this->isLocalStatus($object['id'])) {
            if (config('logging.dev_log')) {
                Log::warning('Update activity rejected - target is not a local status', [
                    'actor' => $activity['actor'],
                    'object_id' => $object['id'],
                ]);
            }

            return false;
        }

        if (isset($object['content']) && ! is_string($object['content'])) {
            return false;
        }

        if (isset($object['summary']) && ! is_string($object['summary'])) {
            return false;
        }

        if (isset($object['sensitive']) && ! is_bool($object['sensitive'])) {
            return false;
        }

        if (isset($object['updated']) && ! is_string($object['updated'])) {
            return false;
        }

        try {
            Carbon::parse($object['updated']);
        } catch (InvalidFormatException $e) {
            return false;
        }

        return true;
    }

    private function validateProfileUpdate(array $activity, array $object): bool
    {
        if (! $this->isLocalProfile($object['id'])) {
            if (config('logging.dev_log')) {
                Log::warning('Update activity rejected - target is not a local profile', [
                    'actor' => $activity['actor'],
                    'object_id' => $object['id'],
                ]);
            }

            return false;
        }

        if (isset($object['name']) && ! is_string($object['name'])) {
            return false;
        }

        if (isset($object['summary']) && ! is_string($object['summary'])) {
            return false;
        }

        if (isset($object['icon']) && (! is_array($object['icon']) || ! isset($object['icon']['url']))) {
            return false;
        }

        return true;
    }

    private function validateGenericUpdate(array $activity, array $object): bool
    {
        if (config('logging.dev_log')) {
            Log::info('Generic Update activity validation - unknown type', [
                'actor' => $activity['actor'],
                'object_type' => $object['type'],
            ]);
        }

        return false;
    }

    /**
     * Check if the given URL represents a local profile
     */
    private function isLocalProfile(string $url): bool
    {
        $isLocal = $this->isLocalObject($url);

        if ($isLocal) {
            return false;
        }

        return Profile::where('uri', $url)->exists();
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
        }

        $commentMatch = Comment::where('ap_id', $url)->exists();
        if ($commentMatch) {
            return true;
        }

        $commentReplyMatch = CommentReply::where('ap_id', $url)->exists();
        if ($commentReplyMatch) {
            return true;
        }

        return Video::where('uri', $url)->exists();
    }
}
