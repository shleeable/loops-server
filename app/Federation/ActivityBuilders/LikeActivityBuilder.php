<?php

namespace App\Federation\ActivityBuilders;

use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\CommentReply;
use App\Models\CommentReplyLike;
use App\Models\Profile;
use App\Models\Video;
use App\Models\VideoLike;

class LikeActivityBuilder
{
    /**
     * Build a Like activity
     *
     * @param  Profile  $actor  The local profile liking the object
     * @param  string  $objectUrl  The URL of the object being liked
     * @param  int  $videoLikeId  The id of the videoLike
     * @return array The ActivityPub Like activity
     */
    public static function build(Profile $actor, string $objectUrl, $videoLikeId): array
    {
        $activityId = $actor->getActorId('#likes/'.$videoLikeId);

        return [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $activityId,
            'type' => 'Like',
            'actor' => $actor->getActorId(),
            'object' => $objectUrl,
        ];
    }

    /**
     * Build a Like activity for a Video
     *
     * @param  Profile  $actor  The local profile liking the video
     * @param  Video  $video  The video being liked
     * @param  VideoLike  $videoLike  The videoLike
     * @return array The ActivityPub Like activity
     */
    public static function buildForVideo(Profile $actor, Video $video, VideoLike $videoLike): array
    {
        return self::build($actor, $video->getObjectUrl(), $videoLike->id);
    }

    /**
     * Build a Like activity for a Comment
     *
     * @param  Profile  $actor  The local profile liking the comment reply
     * @param  string  $objectUrl  The comment being liked
     * @param  CommentLike  $commentLike  The commentLike
     * @return array The ActivityPub Like activity
     */
    public static function buildForComment(Profile $actor, $objectUrl, CommentLike $commentLike): array
    {
        return self::build($actor, $objectUrl, $commentLike->id);
    }

    /**
     * Build a Like activity for a CommentReply
     *
     * @param  Profile  $actor  The local profile liking the comment reply
     * @param  string  $objectUrl  The commentReply being liked
     * @param  CommentReplyLike  $commentReplyLike  The commentReplyLike
     * @return array The ActivityPub Like activity
     */
    public static function buildForCommentReply(Profile $actor, $objectUrl, CommentReplyLike $commentReplyLike): array
    {
        return self::build($actor, $objectUrl, $commentReplyLike->id);
    }
}
