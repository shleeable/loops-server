<?php

namespace App\Observers;

use App\Models\Hashtag;
use App\Models\VideoHashtag;

class VideoHashtagObserver
{
    public function created(VideoHashtag $pivot): void
    {
        $count = VideoHashtag::whereHashtagId($pivot->hashtag_id)->count();
        Hashtag::where('id', $pivot->hashtag_id)->update(['count' => $count]);
    }

    public function updated(VideoHashtag $pivot): void
    {
        $count = VideoHashtag::whereHashtagId($pivot->hashtag_id)->count();
        Hashtag::where('id', $pivot->hashtag_id)->update(['count' => $count]);
    }

    public function deleted(VideoHashtag $pivot): void
    {
        $count = VideoHashtag::whereHashtagId($pivot->hashtag_id)->count();
        Hashtag::where('id', $pivot->hashtag_id)->update(['count' => $count]);
    }
}
