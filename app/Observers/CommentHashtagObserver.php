<?php

namespace App\Observers;

use App\Models\CommentHashtag;
use App\Models\Hashtag;

class CommentHashtagObserver
{
    /**
     * Handle the CommentHashtag "created" event.
     */
    public function created(CommentHashtag $commentHashtag): void
    {
        $count = CommentHashtag::whereHashtagId($commentHashtag->hashtag_id)->count();
        Hashtag::where('id', $commentHashtag->hashtag_id)->update(['comment_count' => $count]);
    }

    /**
     * Handle the CommentHashtag "updated" event.
     */
    public function updated(CommentHashtag $commentHashtag): void
    {
        $count = CommentHashtag::whereHashtagId($commentHashtag->hashtag_id)->count();
        Hashtag::where('id', $commentHashtag->hashtag_id)->update(['comment_count' => $count]);
    }

    /**
     * Handle the CommentHashtag "deleted" event.
     */
    public function deleted(CommentHashtag $commentHashtag): void
    {
        $count = CommentHashtag::whereHashtagId($commentHashtag->hashtag_id)->count();
        Hashtag::where('id', $commentHashtag->hashtag_id)->update(['comment_count' => $count]);
    }

    /**
     * Handle the CommentHashtag "restored" event.
     */
    public function restored(CommentHashtag $commentHashtag): void
    {
        $count = CommentHashtag::whereHashtagId($commentHashtag->hashtag_id)->count();
        Hashtag::where('id', $commentHashtag->hashtag_id)->update(['comment_count' => $count]);
    }

    /**
     * Handle the CommentHashtag "force deleted" event.
     */
    public function forceDeleted(CommentHashtag $commentHashtag): void
    {
        $count = CommentHashtag::whereHashtagId($commentHashtag->hashtag_id)->count();
        Hashtag::where('id', $commentHashtag->hashtag_id)->update(['comment_count' => $count]);
    }
}
