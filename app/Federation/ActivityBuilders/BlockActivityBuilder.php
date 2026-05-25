<?php

namespace App\Federation\ActivityBuilders;

use App\Models\Profile;

class BlockActivityBuilder
{
    /**
     * Build a Block activity
     *
     * @param  Profile  $actor  The local profile doing the blocking
     * @param  string  $targetActorUrl  The actor being blocked
     * @param  string|int|null  $blockId  Identifier for this block (typically the UserFilter row id)
     * @return array The ActivityPub Block activity
     */
    public static function build(Profile $actor, string $targetActorUrl, $blockId = null): array
    {
        $activityId = $actor->getActorId('#blocks/'.$blockId);

        return [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $activityId,
            'type' => 'Block',
            'actor' => $actor->getActorId(),
            'object' => $targetActorUrl,
            'to' => $targetActorUrl,
        ];
    }
}
