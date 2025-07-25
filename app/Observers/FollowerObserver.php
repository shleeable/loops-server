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
        FollowerService::refreshAndSync($follower->profile_id, $follower->following_id);
    }

    /**
     * Handle the Follower "updated" event.
     */
    public function updated(Follower $follower): void
    {
        FollowerService::refreshAndSync($follower->profile_id, $follower->following_id);
    }

    /**
     * Handle the Follower "deleted" event.
     */
    public function deleted(Follower $follower): void
    {
        FollowerService::refreshAndSync($follower->profile_id, $follower->following_id);
    }

    /**
     * Handle the Follower "restored" event.
     */
    public function restored(Follower $follower): void
    {
        FollowerService::refreshAndSync($follower->profile_id, $follower->following_id);
    }

    /**
     * Handle the Follower "force deleted" event.
     */
    public function forceDeleted(Follower $follower): void
    {
        FollowerService::refreshAndSync($follower->profile_id, $follower->following_id);
    }
}
