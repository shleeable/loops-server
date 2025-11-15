<?php

namespace App\Observers;

use App\Models\CommentReplyHashtag;
use App\Models\Hashtag;

class CommentReplyHashtagObserver
{
    /**
     * Handle the CommentReplyHashtag "created" event.
     */
    public function created(CommentReplyHashtag $CommentReplyHashtag): void
    {
        $count = CommentReplyHashtag::whereHashtagId($CommentReplyHashtag->hashtag_id)->count();
        Hashtag::where('id', $CommentReplyHashtag->hashtag_id)->update(['reply_count' => $count]);
    }

    /**
     * Handle the CommentReplyHashtag "updated" event.
     */
    public function updated(CommentReplyHashtag $CommentReplyHashtag): void
    {
        $count = CommentReplyHashtag::whereHashtagId($CommentReplyHashtag->hashtag_id)->count();
        Hashtag::where('id', $CommentReplyHashtag->hashtag_id)->update(['reply_count' => $count]);
    }

    /**
     * Handle the CommentReplyHashtag "deleted" event.
     */
    public function deleted(CommentReplyHashtag $CommentReplyHashtag): void
    {
        $count = CommentReplyHashtag::whereHashtagId($CommentReplyHashtag->hashtag_id)->count();
        Hashtag::where('id', $CommentReplyHashtag->hashtag_id)->update(['reply_count' => $count]);
    }

    /**
     * Handle the CommentReplyHashtag "restored" event.
     */
    public function restored(CommentReplyHashtag $CommentReplyHashtag): void
    {
        $count = CommentReplyHashtag::whereHashtagId($CommentReplyHashtag->hashtag_id)->count();
        Hashtag::where('id', $CommentReplyHashtag->hashtag_id)->update(['reply_count' => $count]);
    }

    /**
     * Handle the CommentReplyHashtag "force deleted" event.
     */
    public function forceDeleted(CommentReplyHashtag $CommentReplyHashtag): void
    {
        $count = CommentReplyHashtag::whereHashtagId($CommentReplyHashtag->hashtag_id)->count();
        Hashtag::where('id', $CommentReplyHashtag->hashtag_id)->update(['reply_count' => $count]);
    }
}
