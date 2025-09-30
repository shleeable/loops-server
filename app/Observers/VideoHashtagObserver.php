<?php

namespace App\Observers;

use App\Models\Hashtag;
use App\Models\VideoHashtag;

class VideoHashtagObserver
{
    public function created(VideoHashtag $pivot): void
    {
        Hashtag::where('id', $pivot->hashtag_id)->increment('count');
    }

    public function deleted(VideoHashtag $pivot): void
    {
        Hashtag::where('id', $pivot->hashtag_id)->decrement('count');
    }
}
