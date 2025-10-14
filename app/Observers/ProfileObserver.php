<?php

namespace App\Observers;

use App\Jobs\Federation\FetchRemoteAvatarJob;
use App\Models\Profile;
use App\Services\AccountService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class ProfileObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the Profile "created" event.
     */
    public function created(Profile $profile): void
    {
        AccountService::del($profile->id);

        if (! $profile->local && $profile->uri && $profile->avatar) {
            FetchRemoteAvatarJob::dispatch($profile)->onQueue('actor-update');
        }
    }

    /**
     * Handle the Profile "updated" event.
     */
    public function updated(Profile $profile): void
    {
        AccountService::del($profile->id);

        if (! $profile->local && $profile->uri && $profile->avatar) {
            FetchRemoteAvatarJob::dispatch($profile)->onQueue('actor-update');
        }
    }

    /**
     * Handle the Profile "deleted" event.
     */
    public function deleted(Profile $profile): void
    {
        AccountService::del($profile->id);
    }

    /**
     * Handle the Profile "restored" event.
     */
    public function restored(Profile $profile): void
    {
        AccountService::del($profile->id);
    }

    /**
     * Handle the Profile "force deleted" event.
     */
    public function forceDeleted(Profile $profile): void
    {
        AccountService::del($profile->id);
    }
}
