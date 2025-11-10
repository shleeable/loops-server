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
    /**
     * Validates an incoming Create activity.
     * Assumes the object is a 'Note' (reply) and validates that its reply
     * chain eventually terminates at a local Video.
     *
     * @throws \Exception if the activity is invalid.
     */
    public function validate(array $activity): void
    {
        if (! $this->hasRequiredFields($activity, ['type', 'actor', 'object'])) {
            throw new \Exception('Create activity is missing required fields: type, actor, or object.');
        }

        if ($activity['type'] !== 'Create') {
            throw new \Exception("Invalid activity type: expected 'Create', got '{$activity['type']}'.");
        }

        if (! is_array($activity['object'])) {
            throw new \Exception('Create activity "object" must be an array.');
        }

        if (! app(SanitizeService::class)->url($activity['actor'], true)) {
            throw new \Exception('Create activity "actor" URI is invalid.');
        }

        $object = $activity['object'];

        if (empty($object['type']) || $object['type'] !== 'Note') {
            throw new \Exception("Create activity 'object' type must be 'Note'. Got '{$object['type']}'.");
        }

        if (empty($object['inReplyTo'])) {
            throw new \Exception("Create 'Note' activity is missing 'inReplyTo' property.");
        }

        if (! is_string($object['inReplyTo'])) {
            throw new \Exception("Create 'Note' activity 'inReplyTo' property must be a string URI.");
        }

        $this->validateReplyChain($object['inReplyTo']);
    }

    /**
     * Walk the reply chain to ensure it eventually leads to a local Video.
     *
     * @throws \Exception if the chain is invalid or does not lead to a local Video.
     */
    private function validateReplyChain(string $inReplyToUrl, int $depth = 0): void
    {
        if ($depth > 10) {
            throw new \Exception("Reply chain depth exceeded (10) while checking '{$inReplyToUrl}'.");
        }

        if (! app(SanitizeService::class)->url($inReplyToUrl, true)) {
            throw new \Exception("Invalid URL in reply chain: '{$inReplyToUrl}'.");
        }

        $sanitizeService = app(SanitizeService::class);

        if ($this->isLocalObject($inReplyToUrl)) {
            $hashIdMatch = $sanitizeService->matchUrlTemplate(
                url: $inReplyToUrl,
                templates: [
                    '/v/{hashId}',
                ],
                useAppHost: true,
                constraints: ['hashId' => '[0-9a-zA-Z_-]{1,11}']
            );

            if ($hashIdMatch) {
                $decodedId = HashidService::safeDecode($hashIdMatch['hashId']);
                if ($decodedId !== null && Video::where('id', $decodedId)->exists()) {
                    return;
                }
            }

            $videoMatch = $sanitizeService->matchUrlTemplate(
                url: $inReplyToUrl,
                templates: [
                    '/ap/users/{pId}/video/{vId}',
                ],
                useAppHost: true,
                constraints: ['pId' => '\d+', 'vId' => '\d+']
            );

            if ($videoMatch && Video::where('id', $videoMatch['vId'])->exists()) {
                return;
            }

            $commentMatch = $sanitizeService->matchUrlTemplate(
                url: $inReplyToUrl,
                templates: [
                    '/ap/users/{pId}/comment/{cId}',
                ],
                useAppHost: true,
                constraints: ['pId' => '\d+', 'cId' => '\d+']
            );

            if ($commentMatch && Comment::where('id', $commentMatch['cId'])->exists()) {
                return;
            }

            $replyMatch = $sanitizeService->matchUrlTemplate(
                url: $inReplyToUrl,
                templates: [
                    '/ap/users/{pId}/reply/{rId}',
                ],
                useAppHost: true,
                constraints: ['pId' => '\d+', 'rId' => '\d+']
            );

            if ($replyMatch && CommentReply::where('id', $replyMatch['rId'])->exists()) {
                return;
            }

            throw new \Exception("Local object '{$inReplyToUrl}' is not a valid reply target.");
        }

        $comment = Comment::where('ap_id', $inReplyToUrl)->first();
        if ($comment) {
            if ($comment->video()->exists()) {
                return;
            }
            throw new \Exception("Known remote comment '{$inReplyToUrl}' is orphaned (no local video).");
        }

        $commentReply = CommentReply::where('ap_id', $inReplyToUrl)->first();
        if ($commentReply) {
            $parentComment = $commentReply->parent;
            if (! $parentComment) {
                throw new \Exception("Known remote reply '{$inReplyToUrl}' is orphaned (parent comment not found).");
            }
            $this->validateReplyChain($parentComment->getObjectUrl(), $depth + 1);

            return;
        }

        try {
            $remoteObject = app(ActivityPubService::class)->get($inReplyToUrl);

            if (! $remoteObject) {
                throw new \Exception("Failed to fetch remote object '{$inReplyToUrl}'.");
            }

            if (empty($remoteObject['inReplyTo'])) {
                throw new \Exception("Remote object '{$inReplyToUrl}' is not a reply, chain terminated.");
            }

            $this->validateReplyChain($remoteObject['inReplyTo'], $depth + 1);

        } catch (\Exception $e) {
            if (config('logging.dev_log')) {
                Log::error("Error fetching remote object '{$inReplyToUrl}'", ['error' => $e->getMessage()]);
            }
            throw new \Exception("Error during reply chain validation: {$e->getMessage()}");
        }
    }
}
