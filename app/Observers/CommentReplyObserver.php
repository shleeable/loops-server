<?php

namespace App\Observers;

use App\Models\CommentReply;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class CommentReplyObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the CommentReply "created" event.
     */
    public function created(CommentReply $commentReply): void
    {
        $video = $commentReply->video;
        $parent = $commentReply->parent;

        // Send to Video Author
        if ($video->is_local && $commentReply->profile_id != $video->profile_id) {
            NotificationService::newVideoCommentReply(
                $commentReply->profile_id,
                $video->profile_id,
                $video->id,
                $commentReply->comment_id,
                $commentReply->id,
            );
        }

        // Send to Comment Parent Author
        if ($parent->ap_id == null
            && $commentReply->profile_id != $parent->profile_id
            && $commentReply->profile_id != $video->profile_id
            && $parent->profile_id != $video->profile_id
        ) {
            NotificationService::newCommentReply(
                $commentReply->profile_id,
                $parent->profile_id,
                $video->id,
                $commentReply->comment_id,
                $commentReply->id,
            );
        }
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
        $types = [Notification::NEW_COMMENT_REPLY, Notification::NEW_VIDCOMMENTREPLY];
        $notifications = Notification::whereIn('type', $types)
            ->whereProfileId($commentReply->profile_id)
            ->whereCommentReplyId($commentReply->id)
            ->get();

        foreach ($notifications as $notification) {
            NotificationService::clearUnreadCount($notification->user_id);
        }

        Notification::whereIn('type', $types)
            ->whereProfileId($commentReply->profile_id)
            ->whereCommentReplyId($commentReply->id)
            ->delete();
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
        $types = [Notification::NEW_COMMENT_REPLY, Notification::NEW_VIDCOMMENTREPLY];
        $notifications = Notification::whereIn('type', $types)
            ->whereProfileId($commentReply->profile_id)
            ->whereCommentReplyId($commentReply->id)
            ->get();

        foreach ($notifications as $notification) {
            NotificationService::clearUnreadCount($notification->user_id);
        }

        Notification::whereIn('type', $types)
            ->whereProfileId($commentReply->profile_id)
            ->whereCommentReplyId($commentReply->id)
            ->delete();
    }
}
