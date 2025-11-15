<?php

namespace App\Federation\ActivityBuilders;

use App\Models\Profile;

class RejectActivityBuilder
{
    /**
     * Build a Reject activity (typically for rejecting follows)
     *
     * @param  Profile  $actor  The local profile rejecting the activity
     * @param  array  $originalActivity  The original activity being rejected
     * @param  string|null  $customId  Optional custom activity ID
     * @return array The ActivityPub Reject activity
     */
    public static function build(Profile $actor, array $originalActivity, ?string $customId = null): array
    {
        $activityId = $customId ?? url('/ap/users/'.$actor->id.'#rejects/'.uniqid());

        return [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $activityId,
            'type' => 'Reject',
            'actor' => $actor->getActorId(),
            'object' => $originalActivity,
        ];
    }

    /**
     * Build a Reject activity for a Follow request
     *
     * @param  Profile  $actor  The local profile rejecting the follow
     * @param  array  $followActivity  The original Follow activity
     * @return array The ActivityPub Reject activity
     */
    public static function buildForFollow(Profile $actor, array $followActivity): array
    {
        $activityId = url('/ap/users/'.$actor->id.'#rejects/follows/'.uniqid());

        return self::build($actor, $followActivity, $activityId);
    }

    /**
     * Build a Reject activity with metadata
     *
     * @param  Profile  $actor  The local profile rejecting the activity
     * @param  array  $originalActivity  The original activity being rejected
     * @param  array  $options  Additional options
     * @return array The ActivityPub Reject activity
     */
    public static function buildWithMetadata(Profile $actor, array $originalActivity, array $options = []): array
    {
        $activity = self::build($actor, $originalActivity, $options['id'] ?? null);

        if (isset($options['published'])) {
            $activity['published'] = $options['published'];
        } else {
            $activity['published'] = now()->toIso8601String();
        }

        if (isset($options['to'])) {
            $activity['to'] = $options['to'];
        }

        return $activity;
    }
}
