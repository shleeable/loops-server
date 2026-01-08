<?php

namespace App\Federation\Validators;

use App\Services\SanitizeService;

class LikeValidator extends BaseValidator
{
    /**
     * Validates an incoming Like activity.
     *
     * @throws \Exception if the activity is invalid.
     */
    public function validate(array $activity): void
    {
        if (! $this->hasRequiredFields($activity, ['type', 'actor', 'object'])) {
            throw new \Exception('Like activity is missing required fields: type, actor, or object.');
        }

        if ($activity['type'] !== 'Like') {
            throw new \Exception("Invalid activity type: expected 'Like', got '{$activity['type']}'.");
        }

        if (! is_string($activity['actor'])) {
            throw new \Exception('Like activity "actor" must be a string URI.');
        }

        if (! is_string($activity['object'])) {
            throw new \Exception('Like activity "object" must be a string URI.');
        }

        if (! app(SanitizeService::class)->url($activity['actor'], true)) {
            throw new \Exception('Like activity "actor" URI is invalid.');
        }

        if (! app(SanitizeService::class)->url($activity['object'], true)) {
            throw new \Exception('Like activity "object" URI is invalid.');
        }
    }
}
