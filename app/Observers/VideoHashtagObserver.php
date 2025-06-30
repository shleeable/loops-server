<?php

namespace App\Observers;

use App\Models\Hashtag;
use Illuminate\Database\Eloquent\Model;

class VideoHashtagObserver
{
    public function created(Model $pivot): void
    {
        Hashtag::where('id', $pivot->hashtag_id)->increment('count');
    }

    public function deleted(Model $pivot): void
    {
        Hashtag::where('id', $pivot->hashtag_id)->decrement('count');
    }
}
