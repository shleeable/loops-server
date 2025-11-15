<?php

namespace App\Services;

class InstanceActorService
{
    public function getActor()
    {
        $domain = parse_url($this->permalink(), PHP_URL_HOST);
        $publicKey = app(SigningService::class)->getPublicKey();

        return [
            '@context' => [
                'https://www.w3.org/ns/activitystreams',
                'https://w3id.org/security/v1',
            ],
            'id' => $this->permalink(),
            'type' => 'Application',
            'inbox' => $this->permalink('/inbox'),
            'outbox' => $this->permalink('/outbox'),
            'preferredUsername' => $domain,
            'publicKey' => [
                'id' => $this->permalink('#main-key'),
                'owner' => $this->permalink(),
                'publicKeyPem' => $publicKey,
            ],
            'manuallyApprovesFollowers' => true,
            'url' => url('/about?instance_actor=true'),
        ];
    }

    public function permalink($suffix = null)
    {
        $url = url('/ap/actor');

        return $url.$suffix;
    }

    public function getActorOutbox()
    {
        return [
            '@context' => [
                'https://www.w3.org/ns/activitystreams',
                'https://w3id.org/security/v1',
            ],
            'id' => $this->permalink('/outbox'),
            'type' => 'OrderedCollection',
            'totalItems' => 0,
            'first' => $this->permalink('/outbox?page=true'),
            'last' => $this->permalink('/outbox?min_id=0page=true'),
        ];
    }
}
