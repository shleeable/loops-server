<?php

namespace App\Federation\Validators;

use App\Services\SanitizeService;

class FollowValidator extends BaseValidator
{
    /**
     * Validates an incoming Follow activity.
     *
     * @throws \Exception if the activity is invalid.
     */
    public function validate(array $activity): void
    {
        if (! $this->hasRequiredFields($activity, ['type', 'actor', 'object'])) {
            throw new \Exception('Follow activity is missing required fields: type, actor, or object.');
        }

        if ($activity['type'] !== 'Follow') {
            throw new \Exception("Invalid activity type: expected 'Follow', got '{$activity['type']}'.");
        }

        if (! is_string($activity['actor'])) {
            throw new \Exception('Follow activity "actor" must be a string URI.');
        }

        if (! is_string($activity['object'])) {
            throw new \Exception('Follow activity "object" must be a string URI.');
        }

        if (! app(SanitizeService::class)->url($activity['actor'], true)) {
            throw new \Exception('Follow activity "actor" URI is invalid.');
        }

        if (! app(SanitizeService::class)->url($activity['object'], true)) {
            throw new \Exception('Follow activity "object" URI is invalid.');
        }

        if (! $this->isLocalProfile($activity['object'])) {
            throw new \Exception("Follow activity rejected: target profile '{$activity['object']}' is not a local profile.");
        }

        if ($activity['actor'] === $activity['object']) {
            throw new \Exception('Follow activity rejected: actor cannot follow themselves.');
        }
    }
}
