<?php

namespace App\Federation\ActivityBuilders;

use App\Models\Profile;
use App\Models\StarterKit;
use App\Models\StarterKitAccount;

class StarterKitMembershipActivityBuilder
{
    public static function buildAdd(
        Profile $owner,
        StarterKit $kit,
        StarterKitAccount $account,
        Profile $member,
    ): array {
        return self::build('Add', $owner, $kit, $account, $member);
    }

    public static function buildRemove(
        Profile $owner,
        StarterKit $kit,
        StarterKitAccount $account,
        Profile $member,
    ): array {
        return self::build('Remove', $owner, $kit, $account, $member);
    }

    private static function build(
        string $type,
        Profile $owner,
        StarterKit $kit,
        StarterKitAccount $account,
        Profile $member,
    ): array {
        $verb = strtolower($type);

        return [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => url('/ap/kit/'.$kit->id.'/items/'.$account->profile_id.'/'.$verb.'#'.$account->id),
            'type' => $type,
            'actor' => $owner->getActorId(),
            'object' => [
                'id' => $account->getPermalink(),
                'type' => 'FeaturedItem',
                'actor' => $member->getActorId(),
            ],
            'target' => $kit->getPermalink(),
        ];
    }
}
