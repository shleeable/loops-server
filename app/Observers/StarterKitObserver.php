<?php

namespace App\Observers;

use App\Models\Notification;
use App\Models\StarterKit;
use App\Services\StarterKitService;

class StarterKitObserver
{
    /**
     * Handle the StarterKit "created" event.
     */
    public function created(StarterKit $starterKit): void
    {
        app(StarterKitService::class)->flushStatsAndPopular();
    }

    /**
     * Handle the StarterKit "updated" event.
     */
    public function updated(StarterKit $starterKit): void
    {
        app(StarterKitService::class)->flushStatsAndPopular();
    }

    /**
     * Handle the StarterKit "deleted" event.
     */
    public function deleted(StarterKit $starterKit): void
    {
        Notification::whereIn('type', Notification::starterKitTypes())->whereNotNull('meta')->where('meta->starter_kit_id', $starterKit->id)->delete();
        app(StarterKitService::class)->flushStatsAndPopular();
        $starterKit->deleteMedia(true);
    }

    /**
     * Handle the StarterKit "restored" event.
     */
    public function restored(StarterKit $starterKit): void
    {
        //
    }

    /**
     * Handle the StarterKit "force deleted" event.
     */
    public function forceDeleted(StarterKit $starterKit): void
    {
        Notification::whereIn('type', Notification::starterKitTypes())->whereNotNull('meta')->where('meta->starter_kit_id', $starterKit->id)->delete();
        $starterKit->deleteMedia(true);
    }
}
