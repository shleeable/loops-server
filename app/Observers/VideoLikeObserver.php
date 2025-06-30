<?php

namespace App\Observers;

use App\Models\VideoLike;
use App\Services\LikeService;

class VideoLikeObserver
{
    /**
     * Handle the VideoLike "created" event.
     */
    public function created(VideoLike $videoLike): void
    {
        $videoLike->video->increment('likes');
        LikeService::addVideo($videoLike->video_id, $videoLike->profile_id);
    }

    /**
     * Handle the VideoLike "deleted" event.
     */
    public function deleted(VideoLike $videoLike): void
    {
        $videoLike->video->decrement('likes');
        LikeService::remVideo($videoLike->video_id, $videoLike->profile_id);
    }

    /**
     * Handle the VideoLike "restored" event.
     */
    public function restored(VideoLike $videoLike): void
    {
        //
    }

    /**
     * Handle the VideoLike "force deleted" event.
     */
    public function forceDeleted(VideoLike $videoLike): void
    {
        $videoLike->video->decrement('likes');
        LikeService::remVideo($videoLike->video_id, $videoLike->profile_id);
    }
}
