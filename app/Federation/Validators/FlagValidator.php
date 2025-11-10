<?php

namespace App\Federation\Validators;

use App\Services\SanitizeService;

class FlagValidator extends BaseValidator
{
    /**
     * Validates an incoming Flag activity.
     *
     * @throws \Exception if the activity is invalid.
     */
    public function validate(array $activity): void
    {
        if (! $this->hasRequiredFields($activity, ['type', 'actor', 'object'])) {
            throw new \Exception('Flag activity is missing required fields: type, actor, or object.');
        }

        if ($activity['type'] !== 'Flag') {
            throw new \Exception("Invalid activity type: expected 'Flag', got '{$activity['type']}'.");
        }

        if (! is_string($activity['actor'])) {
            throw new \Exception('Flag activity "actor" must be a string URI.');
        }

        if (! app(SanitizeService::class)->url($activity['actor'], true)) {
            throw new \Exception('Flag activity "actor" URI is invalid.');
        }

        $objects = is_array($activity['object']) ? $activity['object'] : [$activity['object']];

        if (empty($objects)) {
            throw new \Exception('Flag activity "object" field is empty or missing.');
        }

        foreach ($objects as $index => $object) {
            $this->validateReportableObject($object, $index);
        }

        if (isset($activity['to'])) {
            $recipients = is_array($activity['to']) ? $activity['to'] : [$activity['to']];
            foreach ($recipients as $recipient) {
                if (is_string($recipient) && ! $this->isLocalProfile($recipient)) {
                    throw new \Exception("Flag activity 'to' recipient '{$recipient}' is not a local profile.");
                }
            }
        }
    }

    /**
     * Validate that a single object being reported is valid and local.
     *
     * @param  mixed  $object  The item to validate
     * @param  int  $index  The index from the object array, for error reporting
     *
     * @throws \Exception
     */
    private function validateReportableObject(mixed $object, int $index): void
    {
        if (! is_string($object)) {
            throw new \Exception("Flag activity 'object' at index {$index} is not a string URI.");
        }

        if (! app(SanitizeService::class)->url($object)) {
            throw new \Exception("Flag activity 'object' URI '{$object}' is invalid.");
        }

        if (! $this->isLocalProfile($object) && ! $this->isLocalStatus($object)) {
            throw new \Exception("Flag activity 'object' '{$object}' is not a local profile or local status.");
        }
    }
}
