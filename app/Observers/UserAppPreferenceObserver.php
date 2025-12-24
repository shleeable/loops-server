<?php

namespace App\Observers;

use App\Models\UserAppPreference;
use App\Services\UserAppPreferencesService;

class UserAppPreferenceObserver
{
    public function __construct(
        protected UserAppPreferencesService $preferencesService
    ) {}

    /**
     * Handle the UserAppPreference "updated" event.
     */
    public function updated(UserAppPreference $preference): void
    {
        $this->preferencesService->flushCache($preference->user_id);
    }

    /**
     * Handle the UserAppPreference "deleted" event.
     */
    public function deleted(UserAppPreference $preference): void
    {
        $this->preferencesService->flushCache($preference->user_id);
    }

    /**
     * Handle the UserAppPreference "force deleted" event.
     */
    public function forceDeleted(UserAppPreference $preference): void
    {
        $this->preferencesService->flushCache($preference->user_id);
    }
}
