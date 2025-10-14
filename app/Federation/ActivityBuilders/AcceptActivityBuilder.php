<?php

namespace App\Federation\ActivityBuilders;

use App\Models\Profile;
use Illuminate\Support\Str;

class AcceptActivityBuilder
{
    /**
     * Build an Accept activity (typically for accepting follows)
     *
     * @param  Profile  $actor  The local profile accepting the activity
     * @param  array  $originalActivity  The original activity being accepted
     * @param  string|null  $customId  Optional custom activity ID
     * @return array The ActivityPub Accept activity
     */
    public static function build(Profile $actor, array $originalActivity, ?string $customId = null): array
    {
        $activityId = $customId ?? $actor->getActorId('#accepts/'.Str::uuid());

        return [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $activityId,
            'type' => 'Accept',
            'actor' => $actor->getActorId(),
            'object' => $originalActivity,
        ];
    }

    /**
     * Build an Accept activity for a Follow request
     *
     * @param  Profile  $actor  The local profile accepting the follow
     * @param  array  $followActivity  The original Follow activity
     * @return array The ActivityPub Accept activity
     */
    public static function buildForFollow(Profile $actor, array $followActivity): array
    {
        $activityId = $actor->getActorId('#accepts/follows/'.Str::uuid());

        return self::build($actor, $followActivity, $activityId);
    }

    /**
     * Build an Accept activity with metadata
     *
     * @param  Profile  $actor  The local profile accepting the activity
     * @param  array  $originalActivity  The original activity being accepted
     * @param  array  $options  Additional options
     * @return array The ActivityPub Accept activity
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
