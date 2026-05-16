<?php

namespace App\Federation\Validators;

use App\Services\SanitizeService;

class BlockValidator extends BaseValidator
{
    /**
     * Validates an incoming Block activity.
     *
     * @throws \Exception if the activity is invalid.
     */
    public function validate(array $activity): void
    {
        if (! $this->hasRequiredFields($activity, ['type', 'actor', 'object'])) {
            throw new \Exception('Block activity is missing required fields: type, actor, or object.');
        }

        if ($activity['type'] !== 'Block') {
            throw new \Exception("Invalid activity type: expected 'Block', got '{$activity['type']}'.");
        }

        if (! is_string($activity['actor'])) {
            throw new \Exception('Block activity "actor" must be a string URI.');
        }

        if (! is_string($activity['object'])) {
            throw new \Exception('Block activity "object" must be a string URI.');
        }

        if (! app(SanitizeService::class)->url($activity['actor'], true)) {
            throw new \Exception('Block activity "actor" URI is invalid.');
        }

        if (! app(SanitizeService::class)->url($activity['object'], true)) {
            throw new \Exception('Block activity "object" URI is invalid.');
        }
    }
}
