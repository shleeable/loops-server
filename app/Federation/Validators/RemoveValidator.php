<?php

namespace App\Federation\Validators;

use App\Services\SanitizeService;

class RemoveValidator extends BaseValidator
{
    public function validate(array $activity): void
    {
        if (! $this->hasRequiredFields($activity, ['type', 'actor', 'object', 'target'])) {
            throw new \Exception('Remove activity is missing required fields: type, actor, object, or target.');
        }

        if ($activity['type'] !== 'Remove') {
            throw new \Exception("Invalid activity type: expected 'Remove', got '{$activity['type']}'.");
        }

        if (! is_string($activity['actor'])) {
            throw new \Exception('Remove activity "actor" must be a string URI.');
        }

        $objectUrl = is_array($activity['object']) ? $activity['object']['id'] ?? null : $activity['object'];
        $targetUrl = is_array($activity['target']) ? $activity['target']['id'] ?? null : $activity['target'];

        if (! is_string($objectUrl)) {
            throw new \Exception('Remove activity "object" must be a string URI or an object with an "id" property.');
        }

        if (! is_string($targetUrl)) {
            throw new \Exception('Remove activity "target" must be a string URI or an object with an "id" property.');
        }

        $sanitize = app(SanitizeService::class);

        if (! $sanitize->url($activity['actor'], true)) {
            throw new \Exception('Remove activity "actor" URI is invalid.');
        }

        if (! $sanitize->url($objectUrl, true)) {
            throw new \Exception('Remove activity "object" URI is invalid.');
        }

        if (! $sanitize->url($targetUrl, true)) {
            throw new \Exception('Remove activity "target" URI is invalid.');
        }

        if ($this->isLocalObject($objectUrl)) {
            throw new \Exception("Remove activity rejected: a remote actor cannot remove a local object '{$objectUrl}'.");
        }
    }
}
