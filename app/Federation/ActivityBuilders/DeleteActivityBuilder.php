<?php

namespace App\Federation\ActivityBuilders;

use App\Models\Profile;
use App\Models\Video;

class DeleteActivityBuilder
{
    /**
     * Build a Delete activity for any object
     *
     * @param  Profile  $actor  The local profile deleting the object
     * @param  string  $objectUrl  The URL of the object being deleted
     * @return array The ActivityPub Delete activity
     */
    public static function build(Profile $actor, string $objectUrl): array
    {
        $activityId = url('/ap/users/'.$actor->id.'#deletes/'.uniqid());

        return [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $activityId,
            'type' => 'Delete',
            'actor' => $actor->getActorId(),
            'object' => [
                'id' => $objectUrl,
                'type' => 'Tombstone',
            ],
            'to' => [
                'https://www.w3.org/ns/activitystreams#Public',
            ],
            'cc' => [
                url('/ap/users/'.$actor->id.'/followers'),
            ],
        ];
    }

    /**
     * Build a Delete activity for a Video
     *
     * @param  Profile  $actor  The local profile deleting the video
     * @param  string  $videoObjectUrl  The videoObjectUrl being deleted
     * @return array The ActivityPub Delete activity
     */
    public static function buildForVideo(Profile $actor, string $videoObjectUrl): array
    {
        $activityId = $videoObjectUrl.'#delete';

        return [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $activityId,
            'type' => 'Delete',
            'actor' => $actor->getActorId(),
            'object' => [
                'id' => $videoObjectUrl,
                'type' => 'Tombstone',
            ],
            'to' => [
                'https://www.w3.org/ns/activitystreams#Public',
            ],
        ];
    }

    /**
     * Build a Delete activity for a Comment
     *
     * @param  Profile  $actor  The local profile deleting the video
     * @param  string  $commentObjectUrl  The commentObjectUrl being deleted
     * @return array The ActivityPub Delete activity
     */
    public static function buildForComment(Profile $actor, string $commentObjectUrl): array
    {
        $activityId = $commentObjectUrl.'#delete';

        return [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $activityId,
            'type' => 'Delete',
            'actor' => $actor->getActorId(),
            'object' => [
                'id' => $commentObjectUrl,
                'type' => 'Tombstone',
            ],
            'to' => [
                'https://www.w3.org/ns/activitystreams#Public',
            ],
        ];
    }

    /**
     * Build a Delete activity for a CommentReply
     *
     * @param  Profile  $actor  The local profile deleting the video
     * @param  string  $commentObjectUrl  The commentObjectUrl being deleted
     * @return array The ActivityPub Delete activity
     */
    public static function buildForCommentReply(Profile $actor, string $commentObjectUrl): array
    {
        $activityId = $commentObjectUrl.'#delete';

        return [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $activityId,
            'type' => 'Delete',
            'actor' => $actor->getActorId(),
            'object' => [
                'id' => $commentObjectUrl,
                'type' => 'Tombstone',
            ],
        ];
    }

    /**
     * Build a Delete activity for an account deletion
     *
     * @param  Profile  $actor  The profile being deleted
     * @return array The ActivityPub Delete activity
     */
    public static function buildForAccount(Profile $actor): array
    {
        $activityId = url('/ap/users/'.$actor->id.'#delete');

        return [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $activityId,
            'type' => 'Delete',
            'actor' => $actor->getActorId(),
            'object' => $actor->getActorId(),
            'to' => [
                'https://www.w3.org/ns/activitystreams#Public',
            ],
        ];
    }

    /**
     * Build a Delete activity with custom metadata
     *
     * @param  Profile  $actor  The local profile deleting the object
     * @param  string  $objectUrl  The URL of the object being deleted
     * @param  array  $options  Additional options
     * @return array The ActivityPub Delete activity
     */
    public static function buildWithMetadata(Profile $actor, string $objectUrl, array $options = []): array
    {
        $activity = self::build($actor, $objectUrl);

        if (isset($options['published'])) {
            $activity['published'] = $options['published'];
        }

        if (isset($options['formerType'])) {
            $activity['object']['formerType'] = $options['formerType'];
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
