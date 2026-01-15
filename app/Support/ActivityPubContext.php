<?php

namespace App\Support;

use App\Models\Video;

class ActivityPubContext
{
    public function forVideo(Video $video)
    {
        $visibility = $video->visibility ?? 2;

        return match ($visibility) {
            1 => $this->getVideoPublic(),
            2, 3, 4, 5 => $this->getDefault(),
            default => $this->getDefault(),
        };
    }

    public function forVideoInteractionPolicy(Video $video)
    {
        $visibility = $video->visibility ?? 2;

        return $visibility === 1 ? [
            'canQuote' => [
                'automaticApproval' => [
                    'https://www.w3.org/ns/activitystreams#Public',
                ],
            ],
        ] : [];
    }

    protected function getVideoPublic()
    {
        return [
            'https://www.w3.org/ns/activitystreams',
            'https://w3id.org/security/v1',
            [
                'quote' => 'https://w3id.org/fep/044f#quote',
                'quoteUri' => 'http://fedibird.com/ns#quoteUri',
                '_misskey_quote' => 'https://misskey-hub.net/ns#_misskey_quote',
                'quoteAuthorization' => [
                    '@id' => 'https://w3id.org/fep/044f#quoteAuthorization',
                    '@type' => '@id',
                ],
                'gts' => 'https://gotosocial.org/ns#',
                'interactionPolicy' => [
                    '@id' => 'gts:interactionPolicy',
                    '@type' => '@id',
                ],
                'canQuote' => [
                    '@id' => 'gts:canQuote',
                    '@type' => '@id',
                ],
                'automaticApproval' => [
                    '@id' => 'gts:automaticApproval',
                    '@type' => '@id',
                ],
                'manualApproval' => [
                    '@id' => 'gts:manualApproval',
                    '@type' => '@id',
                ],
            ],
        ];
    }

    protected function getDefault()
    {
        return [
            'https://www.w3.org/ns/activitystreams',
            'https://w3id.org/security/v1',
        ];
    }
}
