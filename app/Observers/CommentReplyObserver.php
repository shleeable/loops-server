<?php

namespace App\Observers;

use App\Models\CommentReply;
use App\Models\Notification;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class CommentReplyObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the CommentReply "created" event.
     */
    public function created(CommentReply $commentReply): void
    {
        //
    }

    /**
     * Handle the CommentReply "updated" event.
     */
    public function updated(CommentReply $commentReply): void
    {
        //
    }

    /**
     * Handle the CommentReply "deleted" event.
     */
    public function deleted(CommentReply $commentReply): void
    {
        Notification::whereCommentReplyId($commentReply->id)->delete();
    }

    /**
     * Handle the CommentReply "restored" event.
     */
    public function restored(CommentReply $commentReply): void
    {
        //
    }

    /**
     * Handle the CommentReply "force deleted" event.
     */
    public function forceDeleted(CommentReply $commentReply): void
    {
        Notification::whereCommentReplyId($commentReply->id)->delete();
    }
}
