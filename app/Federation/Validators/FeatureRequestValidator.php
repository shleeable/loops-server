<?php

namespace App\Federation\Validators;

use App\Services\SanitizeService;

class FeatureRequestValidator extends BaseValidator
{
    /**
     * Validates an incoming Flag activity.
     *
     * @throws \Exception if the activity is invalid.
     */
    public function validate(array $activity): void
    {
        if (! $this->hasRequiredFields($activity, ['type', 'instrument', 'object'])) {
            throw new \Exception('FeatureRequest activity is missing required fields: type, instrument, or object.');
        }

        if ($activity['type'] !== 'FeatureRequest') {
            throw new \Exception("Invalid activity type: expected 'FeatureRequest', got '{$activity['type']}'.");
        }

        if (! is_string($activity['instrument'])) {
            throw new \Exception('FeatureRequest activity "instrument" must be a string URI.');
        }

        if (! is_string($activity['object'])) {
            throw new \Exception('FeatureRequest activity "object" must be a string URI.');
        }

        if (! app(SanitizeService::class)->url($activity['instrument'], true)) {
            throw new \Exception('FeatureRequest activity "instrument" URI is invalid.');
        }

        if (! app(SanitizeService::class)->url($activity['object'], true)) {
            throw new \Exception('FeatureRequest activity "object" URI is invalid.');
        }

        if (! app(SanitizeService::class)->isLocalObject($activity['object'])) {
            throw new \Exception('FeatureRequest activity "object" URI is not local.');
        }
    }
}
