<?php

namespace App\Federation\ActivityBuilders;

use App\Federation\Audience;
use App\Models\Profile;

class DeleteActivityBuilder
{
    /**
     * Build a Delete activity for any object
     *
     * @param  Profile  $actor  The local profile deleting the object
     * @param  string  $objectUrl  The URL of the object being deleted
     * @param  int  $visibility  Visibility of the object being deleted
     * @param  array  $mentions  Actor URIs mentioned on the original object
     * @return array The ActivityPub Delete activity
     */
    public static function build(Profile $actor, string $objectUrl, int $visibility = 1, array $mentions = []): array
    {
        $activityId = url('/ap/users/'.$actor->id.'#deletes/'.uniqid());

        return self::tombstone($actor, $activityId, $objectUrl, $visibility, $mentions);
    }

    /**
     * Build a Delete activity for a Video
     *
     * @param  Profile  $actor  The local profile deleting the video
     * @param  string  $videoObjectUrl  The videoObjectUrl being deleted
     * @param  int  $visibility  Visibility of the video being deleted
     * @param  array  $mentions  Actor URIs mentioned on the original video
     * @return array The ActivityPub Delete activity
     */
    public static function buildForVideo(Profile $actor, string $videoObjectUrl, int $visibility = 1, array $mentions = []): array
    {
        return self::tombstone($actor, $videoObjectUrl.'#delete', $videoObjectUrl, $visibility, $mentions);
    }

    /**
     * Build a Delete activity for a Comment
     *
     * @param  Profile  $actor  The local profile deleting the comment
     * @param  string  $commentObjectUrl  The commentObjectUrl being deleted
     * @param  int  $visibility  Visibility of the comment being deleted
     * @param  array  $mentions  Actor URIs mentioned on the original comment
     * @return array The ActivityPub Delete activity
     */
    public static function buildForComment(Profile $actor, string $commentObjectUrl, int $visibility = 1, array $mentions = []): array
    {
        return self::tombstone($actor, $commentObjectUrl.'#delete', $commentObjectUrl, $visibility, $mentions);
    }

    /**
     * Build a Delete activity for a CommentReply
     *
     * @param  Profile  $actor  The local profile deleting the comment reply
     * @param  string  $commentObjectUrl  The commentObjectUrl being deleted
     * @param  int  $visibility  Visibility of the comment reply being deleted
     * @param  array  $mentions  Actor URIs mentioned on the original comment reply
     * @return array The ActivityPub Delete activity
     */
    public static function buildForCommentReply(Profile $actor, string $commentObjectUrl, int $visibility = 1, array $mentions = []): array
    {
        return self::tombstone($actor, $commentObjectUrl.'#delete', $commentObjectUrl, $visibility, $mentions);
    }

    /**
     * Build a Delete activity for an account deletion
     *
     * Account deletes are always public: every instance holding a cached copy
     * of the actor needs to act on it.
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
        $activity = self::build(
            $actor,
            $objectUrl,
            $options['visibility'] ?? 1,
            $options['mentions'] ?? []
        );

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

    /**
     * Shared Tombstone envelope, addressed to mirror the original object.
     *
     * Receiving implementations use the Delete's addressing for routing and
     * forwarding decisions, so it has to match the audience the object was
     * originally sent to rather than defaulting to Public.
     */
    protected static function tombstone(Profile $actor, string $activityId, string $objectUrl, int $visibility, array $mentions): array
    {
        $audience = Audience::getAudience($visibility, $actor->getFollowersUrl(), $mentions);

        return [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $activityId,
            'type' => 'Delete',
            'actor' => $actor->getActorId(),
            'published' => now()->toIso8601ZuluString(),
            'object' => [
                'id' => $objectUrl,
                'type' => 'Tombstone',
            ],
            'to' => $audience['to'],
            'cc' => $audience['cc'],
        ];
    }
}
