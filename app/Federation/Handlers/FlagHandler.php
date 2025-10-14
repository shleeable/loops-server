<?php

namespace App\Federation\Handlers;

use App\Models\Comment;
use App\Models\CommentReply;
use App\Models\Profile;
use App\Models\Report;
use App\Models\Video;
use App\Services\SanitizeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FlagHandler
{
    public function handle(array $activity, Profile $actor): bool
    {
        try {
            DB::beginTransaction();

            $objects = is_array($activity['object']) ? $activity['object'] : [$activity['object']];

            $reason = 'No reason provided';

            if (isset($activity['content']) && ! empty($activity['content'])) {
                $reason = Str::limit(app(SanitizeService::class)->cleanHtmlWithSpacing($activity['content']), 5000);
            }

            foreach ($objects as $objectUrl) {
                $this->createReport($actor, $objectUrl, $reason, $activity);
            }

            DB::commit();

            if (config('logging.dev_log')) {
                Log::info('ActivityPub Flag processed', [
                    'reporter' => $actor->uri ?? $actor->username,
                    'objects_count' => count($objects),
                    'reason' => $reason,
                ]);
            }

            return true;

        } catch (\Exception $e) {
            DB::rollBack();

            if (config('logging.dev_log')) {
                Log::error('Failed to process Flag activity', [
                    'error' => $e->getMessage(),
                    'actor' => $actor->uri ?? $actor->username,
                    'activity_id' => $activity['id'] ?? null,
                ]);
            }

            return false;
        }
    }

    /**
     * Create a report for the given object
     */
    private function createReport(Profile $reporter, string $objectUrl, string $reason, array $activity): void
    {
        $reportableData = $this->getReportableData($objectUrl);

        if (! $reportableData || ! isset($reportableData['id'], $reportableData['type'], $reportableData['profile_id'])) {
            if (config('logging.dev_log')) {
                Log::warning('Could not determine reportable content type', [
                    'object_url' => $objectUrl,
                    'reporter' => $reporter->username,
                ]);
            }

            return;
        }

        $objectType = match ($reportableData['type']) {
            'profile' => 'reported_profile_id',
            'video' => 'reported_video_id',
            'comment' => 'reported_comment_id',
            'comment_reply' => 'reported_comment_reply_id',
            default => null,
        };

        if (! $objectType) {
            return;
        }

        $existingReport = Report::where('reporter_profile_id', $reporter->id)
            ->where($objectType, $reportableData['id'])
            ->exists();

        if ($existingReport) {
            // Report already exists
            return;
        }

        $reportDomain = $reporter->uri ? parse_url($reporter->uri, PHP_URL_HOST) : null;

        $report = Report::create([
            'reporter_profile_id' => $reporter->id,
            'reported_profile_id' => $reportableData['profile_id'],
            $objectType => $reportableData['id'],
            'report_type' => 1026,
            'is_remote' => true,
            'user_message' => $reason,
            'domain' => $reportDomain,
        ]);
    }

    /**
     * Get reportable content data from URL
     */
    private function getReportableData(string $objectUrl): ?array
    {
        if ($profileData = $this->getProfileData($objectUrl)) {
            return $profileData;
        }

        if ($videoData = $this->getVideoData($objectUrl)) {
            return $videoData;
        }

        if ($commentData = $this->getCommentData($objectUrl)) {
            return $commentData;
        }

        if ($replyData = $this->getCommentReplyData($objectUrl)) {
            return $replyData;
        }

        return null;
    }

    /**
     * Get profile data from URL
     */
    private function getProfileData(string $url): ?array
    {
        $profileMatch = app(SanitizeService::class)->matchUrlTemplate(
            url: $url,
            templates: [
                '/ap/users/{profileId}',
            ],
            useAppHost: true,
            constraints: ['profileId' => '\d+', 'username' => '[a-zA-Z0-9_]+']
        );

        if ($profileMatch && isset($profileMatch['profileId'])) {
            $profile = Profile::where('id', $profileMatch['profileId'])->whereLocal(true)->first();
        } else {
            $profile = null;
        }

        if ($profile) {
            return [
                'type' => 'profile',
                'id' => $profile->id,
                'profile_id' => $profile->id,
            ];
        }

        return null;
    }

    /**
     * Get video data from URL
     */
    private function getVideoData(string $url): ?array
    {
        $videoMatch = app(SanitizeService::class)->matchUrlTemplate(
            url: $url,
            templates: [
                '/ap/users/{userId}/video/{videoId}',
            ],
            useAppHost: true,
            constraints: ['userId' => '\d+', 'videoId' => '\d+']
        );

        if ($videoMatch && isset($videoMatch['userId'], $videoMatch['videoId'])) {
            $video = Video::whereProfileId($videoMatch['userId'])->whereKey($videoMatch['videoId'])->first();
        } else {
            $video = null;
        }

        if ($video) {
            return [
                'type' => 'video',
                'id' => $video->id,
                'profile_id' => $video->profile_id,
            ];
        }

        return null;
    }

    /**
     * Get comment data from URL
     */
    private function getCommentData(string $url): ?array
    {
        $commentMatch = app(SanitizeService::class)->matchUrlTemplate(
            url: $url,
            templates: [
                '/ap/users/{userId}/comment/{replyId}',
            ],
            useAppHost: true,
            constraints: ['userId' => '\d+', 'replyId' => '\d+']
        );

        if ($commentMatch && isset($commentMatch['userId'], $commentMatch['replyId'])) {
            $comment = Comment::whereProfileId($commentMatch['userId'])->whereKey($commentMatch['replyId'])->first();
        } else {
            $comment = null;
        }

        if ($comment) {
            return [
                'type' => 'comment',
                'id' => $comment->id,
                'profile_id' => $comment->profile_id,
            ];
        }

        return null;
    }

    /**
     * Get comment reply data from URL
     */
    private function getCommentReplyData(string $url): ?array
    {
        $commentReplyMatch = app(SanitizeService::class)->matchUrlTemplate(
            url: $url,
            templates: [
                '/ap/users/{userId}/reply/{commentReplyId}',
            ],
            useAppHost: true,
            constraints: ['userId' => '\d+', 'commentReplyId' => '\d+']
        );

        if ($commentReplyMatch && isset($commentReplyMatch['userId'], $commentReplyMatch['commentReplyId'])) {
            $reply = CommentReply::whereProfileId($commentReplyMatch['userId'])->whereKey($commentReplyMatch['commentReplyId'])->first();
        } else {
            $reply = null;
        }

        if ($reply) {
            return [
                'type' => 'comment_reply',
                'id' => $reply->id,
                'profile_id' => $reply->profile_id,
            ];
        }

        return null;
    }
}
