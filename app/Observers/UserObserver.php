<?php

namespace App\Observers;

use App\Jobs\User\UserSystemMessageSeederJob;
use App\Models\Profile;
use App\Models\User;
use App\Services\UserAppPreferencesService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class UserObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $profile = Profile::updateOrCreate([
            'user_id' => $user->id,
        ], [
            'username' => $user->username,
            'name' => $user->name,
            'local' => true,
        ]);
        $user->profile_id = $profile->id;
        $user->save();

        UserSystemMessageSeederJob::dispatch($profile)->onQueue('notify');
        app(UserAppPreferencesService::class)->get($user->id);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
