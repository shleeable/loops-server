<?php

namespace App\Federation\ActivityBuilders;

use App\Models\Profile;

class UndoActivityBuilder
{
    /**
     * Build an Undo activity for any activity
     *
     * @param  Profile  $actor  The local profile undoing the activity
     * @param  array  $originalActivity  The original activity being undone
     * @return array The ActivityPub Undo activity
     */
    public static function build(Profile $actor, array $originalActivity, ?string $baseActivityId): array
    {
        $activityId = $baseActivityId ?? $actor->getActorId('#undo');

        return [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $activityId,
            'type' => 'Undo',
            'actor' => $actor->getActorId(),
            'object' => $originalActivity,
        ];
    }

    /**
     * Build an Undo activity for a Follow
     *
     * @param  Profile  $actor  The local profile unfollowing
     * @param  string  $targetActorUrl  The actor being unfollowed
     * @param  string|null  $originalFollowId  The ID of the original Follow activity
     * @return array The ActivityPub Undo activity
     */
    public static function buildForFollow(Profile $actor, string $targetActorUrl, ?string $originalFollowId = null): array
    {
        $followId = $actor->getActorId('#follows/'.$originalFollowId);
        $baseActivityId = $followId.'/undo';

        $followActivity = [
            'id' => $followId,
            'type' => 'Follow',
            'actor' => $actor->getActorId(),
            'object' => $targetActorUrl,
        ];

        return self::build($actor, $followActivity, $baseActivityId);
    }

    /**
     * Build an Undo activity for a Like
     *
     * @param  Profile  $actor  The local profile unliking
     * @param  string  $objectUrl  The object being unliked
     * @param  string|null  $originalLikeId  The ID of the original Like activity
     * @return array The ActivityPub Undo activity
     */
    public static function buildForLike(Profile $actor, string $objectUrl, ?string $originalLikeId = null): array
    {
        $likeId = $actor->getActorId('#likes/'.$originalLikeId);
        $baseActivityId = $likeId.'/undo';

        $likeActivity = [
            'id' => $likeId,
            'type' => 'Like',
            'actor' => $actor->getActorId(),
            'object' => $objectUrl,
        ];

        return self::build($actor, $likeActivity, $baseActivityId);
    }

    /**
     * Build an Undo activity for an Announce
     *
     * @param  Profile  $actor  The local profile unannouncing
     * @param  string  $objectUrl  The object being unannounced
     * @param  string|null  $originalAnnounceId  The ID of the original Announce activity
     * @return array The ActivityPub Undo activity
     */
    public static function buildForAnnounce(Profile $actor, string $objectUrl, ?string $originalAnnounceId = null): array
    {
        $announceId = $actor->getActorId('#announces/'.$originalAnnounceId);
        $baseActivityId = $announceId.'/undo';

        $announceActivity = [
            'id' => $announceId,
            'type' => 'Announce',
            'actor' => $actor->getActorId(),
            'object' => $objectUrl,
        ];

        return self::build($actor, $announceActivity, $baseActivityId);
    }

    /**
     * Build an Undo activity with metadata
     *
     * @param  Profile  $actor  The local profile undoing the activity
     * @param  array  $originalActivity  The original activity being undone
     * @param  array  $options  Additional options
     * @return array The ActivityPub Undo activity
     */
    public static function buildWithMetadata(Profile $actor, array $originalActivity, array $options = []): array
    {
        $activity = self::build($actor, $originalActivity, null);

        if (isset($options['published'])) {
            $activity['published'] = $options['published'];
        }

        if (isset($options['to'])) {
            $activity['to'] = $options['to'];
        }

        if (isset($options['cc'])) {
            $activity['cc'] = $options['cc'];
        }

        return $activity;
    }
}
