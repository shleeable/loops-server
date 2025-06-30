<?php

namespace App\Observers;

use App\Models\Follower;
use App\Services\AccountService;
use App\Services\FollowerService;

class FollowerObserver
{
    /**
     * Handle the Follower "created" event.
     */
    public function created(Follower $follower): void
    {
        $follower->profile->increment('following');
        $follower->following->increment('followers');
        AccountService::del($follower->profile_id);
        AccountService::del($follower->following_id);
        FollowerService::del($follower->profile_id, $follower->following_id);
    }

    /**
     * Handle the Follower "updated" event.
     */
    public function updated(Follower $follower): void
    {
        AccountService::del($follower->profile_id);
        AccountService::del($follower->following_id);
        FollowerService::del($follower->profile_id, $follower->following_id);
    }

    /**
     * Handle the Follower "deleted" event.
     */
    public function deleted(Follower $follower): void
    {
        $follower->profile->decrement('following');
        $follower->following->decrement('followers');
        AccountService::del($follower->profile_id);
        AccountService::del($follower->following_id);
        FollowerService::del($follower->profile_id, $follower->following_id);
    }

    /**
     * Handle the Follower "restored" event.
     */
    public function restored(Follower $follower): void
    {
        //
    }

    /**
     * Handle the Follower "force deleted" event.
     */
    public function forceDeleted(Follower $follower): void
    {
        //
    }
}
