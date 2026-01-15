<?php

namespace App\Federation\ActivityBuilders;

use App\Models\Profile;
use App\Models\QuoteAuthorization;
use App\Services\SnowflakeService;

class QuoteRequestActivityBuilder
{
    /**
     * Build a QuoteRequest activity (for requesting permission to quote)
     *
     * @param  Profile  $actor  The local profile requesting to quote
     * @param  string  $quotedObjectUrl  The URL of the object being quoted
     * @param  array  $quotePost  The post that contains the quote
     * @return array The ActivityPub QuoteRequest activity
     */
    public static function buildRequest(Profile $actor, string $quotedObjectUrl, array $quotePost): array
    {
        $activityId = $actor->getActorId('#quote-requests/'.SnowflakeService::next());

        return [
            '@context' => [
                'https://www.w3.org/ns/activitystreams',
                [
                    'QuoteRequest' => 'https://w3id.org/fep/044f#QuoteRequest',
                    'quote' => [
                        '@id' => 'https://w3id.org/fep/044f#quote',
                        '@type' => '@id',
                    ],
                ],
            ],
            'type' => 'QuoteRequest',
            'id' => $activityId,
            'actor' => $actor->getActorId(),
            'object' => $quotedObjectUrl,
            'instrument' => $quotePost,
        ];
    }

    /**
     * Build an Accept activity for a QuoteRequest
     *
     * @param  array  $quoteRequest  The original QuoteRequest activity
     * @param  Profile  $actor  The local profile accepting the quote
     * @param  QuoteAuthorization  $authorization  The authorization stamp
     * @return array The ActivityPub Accept activity
     */
    public static function buildAccept(array $quoteRequest, Profile $actor, QuoteAuthorization $authorization): array
    {
        $activityId = $actor->getActorId('#accepts/quote-requests/'.SnowflakeService::next());

        return [
            '@context' => [
                'https://www.w3.org/ns/activitystreams',
                [
                    'QuoteRequest' => 'https://w3id.org/fep/044f#QuoteRequest',
                ],
            ],
            'type' => 'Accept',
            'id' => $activityId,
            'actor' => $actor->getActorId(),
            'to' => [$quoteRequest['actor']],
            'object' => $quoteRequest,
            'result' => $authorization->ap_url,
            'published' => now()->toIso8601String(),
        ];
    }

    /**
     * Build a Reject activity for a QuoteRequest
     *
     * @param  array  $quoteRequest  The original QuoteRequest activity
     * @param  Profile  $actor  The local profile rejecting the quote
     * @return array The ActivityPub Reject activity
     */
    public static function buildReject(array $quoteRequest, Profile $actor): array
    {
        $activityId = $actor->getActorId('#rejects/quote-requests/'.SnowflakeService::next());

        return [
            '@context' => [
                'https://www.w3.org/ns/activitystreams',
                [
                    'QuoteRequest' => 'https://w3id.org/fep/044f#QuoteRequest',
                ],
            ],
            'type' => 'Reject',
            'id' => $activityId,
            'actor' => $actor->getActorId(),
            'to' => [$quoteRequest['actor']],
            'object' => $quoteRequest,
            'published' => now()->toIso8601String(),
        ];
    }
}
