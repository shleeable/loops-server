<?php

namespace App\Federation\Validators;

use App\Services\SanitizeService;
use Illuminate\Support\Facades\Log;

class DeleteValidator extends BaseValidator
{
    public function validate(array $activity): bool
    {
        if (! $this->hasRequiredFields($activity, ['type', 'actor', 'object'])) {
            return false;
        }

        if ($activity['type'] !== 'Delete') {
            return false;
        }

        if (! is_string($activity['actor'])) {
            return false;
        }

        if (is_array($activity['object']) && isset($activity['object']['type'])) {
            $allowedTypes = ['Person', 'Tombstone'];
            if (! in_array($activity['object']['type'], $allowedTypes)) {
                return false;
            }
        }

        $objectUrl = is_array($activity['object']) ? $activity['object']['id'] ?? null : $activity['object'];

        if (! $objectUrl || ! is_string($objectUrl)) {
            return false;
        }

        if (! app(SanitizeService::class)->url($activity['actor'], true)) {
            return false;
        }

        if (! app(SanitizeService::class)->url($objectUrl, true)) {
            return false;
        }

        if ($this->isLocalObject($objectUrl)) {
            if (config('logging.dev_log')) {
                Log::warning('Delete activity rejected - object is local', [
                    'actor' => $activity['actor'],
                    'object' => $objectUrl,
                ]);
            }

            return false;
        }

        return true;
    }
}
