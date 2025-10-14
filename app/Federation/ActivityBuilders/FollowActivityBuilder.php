<?php

namespace App\Federation\ActivityBuilders;

use App\Models\FollowRequest;
use App\Models\Profile;

class FollowActivityBuilder
{
    /**
     * Build a Follow activity
     *
     * @param  Profile  $actor  The local profile initiating the follow
     * @param  string  $targetActorUrl  The remote actor being followed
     * @return array The ActivityPub Follow activity
     */
    public static function build(Profile $actor, string $targetActorUrl, FollowRequest $followRequest): array
    {
        $activityId = $actor->getActorId('#follows/'.$followRequest->id);

        return [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $activityId,
            'type' => 'Follow',
            'actor' => $actor->getActorId(),
            'object' => $targetActorUrl,
        ];
    }
}
