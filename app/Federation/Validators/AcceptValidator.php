<?php

namespace App\Federation\Validators;

use App\Services\SanitizeService;

class AcceptValidator extends BaseValidator
{
    /**
     * Validates an incoming Accept activity.
     * Accept activities are typically sent in response to Follow requests.
     *
     * @throws \Exception if the activity is invalid.
     */
    public function validate(array $activity): void
    {
        if (! $this->hasRequiredFields($activity, ['id', 'type', 'actor', 'object'])) {
            throw new \Exception('Accept activity is missing required fields: id, type, actor, or object.');
        }

        if ($activity['type'] !== 'Accept') {
            throw new \Exception("Invalid activity type: expected 'Accept', got '{$activity['type']}'.");
        }

        if (! is_string($activity['actor'])) {
            throw new \Exception('[AcceptValidator] Accept activity "actor" must be a string URI.');
        }

        $object = $activity['object'];

        if (! is_array($object)) {
            throw new \Exception('[AcceptValidator] Accept activity "object" must be an object (Follow activity).');
        }

        if (! $this->hasRequiredFields($object, ['id', 'type', 'actor', 'object'])) {
            throw new \Exception('[AcceptValidator] Accept activity object (Follow) is missing required fields: id, type, actor, or object.');
        }

        if ($object['type'] !== 'Follow') {
            throw new \Exception("[AcceptValidator] Invalid object type: expected 'Follow', got '{$object['type']}'.");
        }

        if (! is_string($object['actor'])) {
            throw new \Exception('[AcceptValidator] Follow activity "actor" must be a string URI.');
        }

        if (! is_string($object['object'])) {
            throw new \Exception('[AcceptValidator] Follow activity "object" must be a string URI.');
        }

        if ($activity['actor'] !== $object['object']) {
            throw new \Exception('[AcceptValidator] Accept actor must match the Follow object (security validation failed).');
        }

        if (! app(SanitizeService::class)->url($activity['actor'], true)) {
            throw new \Exception('[AcceptValidator] Invalid Accept actor URI.');
        }

        if (! app(SanitizeService::class)->url($object['id'], true)) {
            throw new \Exception('[AcceptValidator] Invalid Follow activity ID.');
        }

        if (! app(SanitizeService::class)->url($object['actor'], true)) {
            throw new \Exception('[AcceptValidator] Invalid Follow actor URI.');
        }

        if (! app(SanitizeService::class)->url($object['object'], true)) {
            throw new \Exception('[AcceptValidator] Invalid Follow object URI.');
        }
    }
}
