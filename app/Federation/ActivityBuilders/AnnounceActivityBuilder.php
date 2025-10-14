<?php

namespace App\Federation\ActivityBuilders;

use App\Models\Comment;
use App\Models\CommentReply;
use App\Models\CommentReplyRepost;
use App\Models\CommentRepost;
use App\Models\Profile;
use App\Models\Video;
use App\Models\VideoRepost;

class AnnounceActivityBuilder
{
    /**
     * Build an Announce activity (boost/share)
     *
     * @param  Profile  $actor  The local profile announcing the object
     * @param  string  $objectUrl  The URL of the object being announced
     * @return array The ActivityPub Announce activity
     */
    public static function build(Profile $actor, string $objectUrl, string $activityId): array
    {
        return [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $activityId,
            'type' => 'Announce',
            'actor' => $actor->getActorId(),
            'object' => $objectUrl,
            'published' => now()->toIso8601String(),
            'to' => [
                'https://www.w3.org/ns/activitystreams#Public',
            ],
            'cc' => [
                $actor->getActorId(),
                $actor->getFollowersUrl(),
            ],
        ];
    }

    /**
     * Build an Announce activity for a Video
     *
     * @param  Profile  $actor  The local profile announcing the video
     * @param  Video  $video  The video being announced
     * @param  VideoRepost  $videoRepost  The VideoRepost activity
     * @return array The ActivityPub Announce activity
     */
    public static function buildForVideo(Profile $actor, Video $video, VideoRepost $videoRepost): array
    {
        $activityId = $actor->getActorId("/video-repost/{$videoRepost->id}/activity");

        $activity = self::build($actor, $video->getObjectUrl(), $activityId);

        if ($video->profile) {
            $activity['cc'][] = $video->profile->getActorId();
            $activity['cc'][] = $video->profile->getFollowersUrl();
        }

        $activity['cc'] = array_values(array_unique($activity['cc']));

        return $activity;
    }

    /**
     * Build an Announce activity for a Comment
     *
     * @param  Profile  $actor  The local profile announcing the video
     * @param  Comment  $comment  The comment being announced
     * @param  CommentRepost  $commentRepost  The CommentRepost activity
     * @return array The ActivityPub Announce activity
     */
    public static function buildForComment(Profile $actor, Comment $comment, CommentRepost $commentRepost): array
    {
        $activityId = $actor->getActorId("/comment-repost/{$commentRepost->id}/activity");

        $activity = self::build($actor, $comment->getObjectUrl(), $activityId);

        if ($comment->profile) {
            $activity['cc'][] = $comment->profile->getActorId();
            $activity['cc'][] = $comment->profile->getFollowersUrl();
        }

        $activity['cc'] = array_values(array_unique($activity['cc']));

        return $activity;
    }

    /**
     * Build an Announce activity for a CommentReply
     *
     * @param  Profile  $actor  The local profile announcing the video
     * @param  CommentReply  $commentReply  The comment reply being announced
     * @param  CommentReplyRepost  $commentReplyRepost  The CommentReplyRepost activity
     * @return array The ActivityPub Announce activity
     */
    public static function buildForCommentReply(Profile $actor, CommentReply $commentReply, CommentReplyRepost $commentReplyRepost): array
    {
        $activityId = $actor->getActorId("/reply-repost/{$commentReplyRepost->id}/activity");

        $activity = self::build($actor, $commentReply->getObjectUrl(), $activityId);

        if ($commentReply->profile) {
            $activity['cc'][] = $commentReply->profile->getActorId();
            $activity['cc'][] = $commentReply->profile->getFollowersUrl();
        }

        $activity['cc'] = array_values(array_unique($activity['cc']));

        return $activity;
    }

    /**
     * Build an Announce activity with custom metadata
     *
     * @param  Profile  $actor  The local profile announcing the object
     * @param  string  $objectUrl  The URL of the object being announced
     * @param  array  $options  Additional options
     * @return array The ActivityPub Announce activity
     */
    public static function buildWithMetadata(Profile $actor, string $objectUrl, $activityId, array $options = []): array
    {
        $activity = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $activityId,
            'type' => 'Announce',
            'actor' => $actor->getActorId(),
            'object' => $objectUrl,
        ];

        if (isset($options['published'])) {
            $activity['published'] = $options['published'];
        } else {
            $activity['published'] = now()->toIso8601String();
        }

        if (isset($options['to'])) {
            $activity['to'] = $options['to'];
        } else {
            $activity['to'] = ['https://www.w3.org/ns/activitystreams#Public'];
        }

        if (isset($options['cc'])) {
            $activity['cc'] = $options['cc'];
        } else {
            $activity['cc'] = [
                $actor->getActorId(),
                $actor->getFollowersUrl(),
            ];
        }

        $activity['cc'] = array_values(array_unique($activity['cc']));

        return $activity;
    }
}
