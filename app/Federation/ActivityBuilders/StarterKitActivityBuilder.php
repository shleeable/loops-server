<?php

namespace App\Federation\ActivityBuilders;

use App\Models\StarterKit;
use App\Models\StarterKitAccount;
use App\Services\AutoLinkerService;
use App\Services\StarterKitService;

class StarterKitActivityBuilder
{
    /**
     * Build a StarterKit activity
     *
     * @param  StarterKit  $kit  The StarterKit
     * @return array The ActivityPub FeaturedCollection activity
     */
    public static function buildKit(StarterKit $kit): array
    {
        $creator = $kit->is_local ? url('/ap/users/'.$kit->profile_id) : null;
        $cached = app(StarterKitService::class)->get($kit->id);

        $hashtags = data_get($cached, 'hashtags', []);
        $accounts = data_get($cached, 'accounts', []);
        $approved = $kit->starterKitAccounts()->where('kit_status', 1)->whereNotNull('approved_at')->orderBy('order')->get()->map(function ($acct) {
            return [
                'id' => $acct->getPermalink(),
                'type' => 'FeaturedItem',
                'featuredObject' => $acct->getAccountPermalink(),
                'featuredObjectType' => 'Person',
                'featureAuthorization' => $acct->getAttestationUrl(),
            ];
        });

        $res = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $kit->getPermalink(),
            'type' => 'FeaturedCollection',
            'name' => $kit->title,
            'summary' => AutoLinkerService::link($kit->description),
            'attributedTo' => $creator,
            'url' => $kit->publicUrl(),
            'sensitive' => $kit->is_sensitive,
            'discoverable' => $kit->is_discoverable,
            'topic' => [],
            'hashtags' => [],
            'icon' => [],
            'image' => [],
            'totalUses' => $kit->uses,
            'totalItems' => count($accounts),
            'orderedItems' => $approved,
            'published' => $kit->created_at->format('c'),
            'updated' => $kit->updated_at->format('c'),
        ];

        if (count($hashtags)) {
            $res['topic'] = [
                'type' => 'Hashtag',
                'name' => '#'.$hashtags[0],
            ];

            $res['hashtags'] = $hashtags;
        } else {
            unset($res['topic']);
            unset($res['hashtags']);
        }

        if ($kit->icon_url) {
            $res['icon'] = [
                'type' => 'Image',
                'url' => [
                    'type' => 'Link',
                    'mediaType' => 'image/webp',
                    'href' => $kit->icon_url,
                ],
            ];
        } else {
            unset($res['icon']);
        }

        if ($kit->header_url) {
            $res['image'] = [
                'type' => 'Image',
                'url' => [
                    'type' => 'Link',
                    'mediaType' => 'image/webp',
                    'href' => $kit->header_url,
                ],
            ];
        } else {
            unset($res['image']);
        }

        return $res;
    }

    public static function buildAccountItem(StarterKitAccount $acct): array
    {
        $res = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $acct->getPermalink(),
            'type' => 'FeaturedItem',
            'object' => $acct->getAccountPermalink(),
            'featureAuthorization' => $acct->getAttestationUrl(),
        ];

        return $res;
    }

    public static function buildAccountAttestation(StarterKitAccount $acct, StarterKit $kit): array
    {
        $res = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $acct->getAttestationUrl(),
            'type' => 'FeatureAuthorization',
            'interactingObject' => $kit->getPermalink(),
            'interactionTarget' => $acct->getAccountPermalink(),
        ];

        return $res;
    }
}
