<?php

namespace App\Federation\Validators;

use App\Models\Profile;
use App\Services\SanitizeService;
use Illuminate\Support\Facades\Log;

class FollowValidator extends BaseValidator
{
    public function validate(array $activity): bool
    {
        if (! $this->hasRequiredFields($activity, ['type', 'actor', 'object'])) {
            return false;
        }

        if ($activity['type'] !== 'Follow') {
            return false;
        }

        if (! is_string($activity['actor'])) {
            return false;
        }

        if (! is_string($activity['object'])) {
            return false;
        }

        if (! app(SanitizeService::class)->url($activity['actor'], true)) {
            return false;
        }

        if (! $this->isLocalProfile($activity['object'])) {
            if (config('logging.dev_log')) {
                Log::warning('Follow activity rejected - target is not a local profile', [
                    'actor' => $activity['actor'],
                    'object' => $activity['object'],
                ]);
            }

            return false;
        }

        if ($activity['actor'] === $activity['object']) {
            return false;
        }

        return true;
    }

    /**
     * Check if the given URL represents a local profile
     */
    private function isLocalProfile(string $url): bool
    {
        $isLocal = $this->isLocalObject($url);

        if (! $isLocal) {
            return false;
        }

        $profileMatch = app(SanitizeService::class)->matchUrlTemplate(
            url: $url,
            templates: [
                '/ap/users/{profileId}',
            ],
            useAppHost: true,
            constraints: ['profileId' => '\d+']
        );

        if ($profileMatch) {
            if (isset($profileMatch['profileId'])) {
                return Profile::where('id', $profileMatch['profileId'])->whereStatus(1)->whereLocal(true)->exists();
            }
        }

        return false;
    }
}
