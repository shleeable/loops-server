<?php

namespace App\Observers;

use App\Models\CommentReplyLike;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class CommentReplyLikeObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the CommentReplyLike "created" event.
     */
    public function created(CommentReplyLike $commentReplyLike): void
    {
        $comment = $commentReplyLike->comment;

        if ($comment && $comment->ap_id == null && $comment->profile_id != $commentReplyLike->profile_id) {
            NotificationService::newCommentReplyLike(
                $comment->profile_id,
                $commentReplyLike->profile_id,
                $commentReplyLike->comment_id,
                $comment->video_id,
            );
        }
    }

    /**
     * Handle the CommentReplyLike "updated" event.
     */
    public function updated(CommentReplyLike $commentReplyLike): void
    {
        //
    }

    /**
     * Handle the CommentReplyLike "deleted" event.
     */
    public function deleted(CommentReplyLike $commentReplyLike): void
    {
        $notifications = Notification::whereType(Notification::VIDEO_COMMENT_REPLY_LIKE)
            ->whereProfileId($commentReplyLike->profile_id)
            ->whereCommentReplyId($commentReplyLike->comment_id)
            ->get();

        foreach ($notifications as $notification) {
            NotificationService::clearUnreadCount($notification->user_id);
        }

        Notification::whereType(Notification::VIDEO_COMMENT_REPLY_LIKE)
            ->whereProfileId($commentReplyLike->profile_id)
            ->whereCommentReplyId($commentReplyLike->comment_id)
            ->delete();
    }

    /**
     * Handle the CommentReplyLike "restored" event.
     */
    public function restored(CommentReplyLike $commentReplyLike): void
    {
        //
    }

    /**
     * Handle the CommentReplyLike "force deleted" event.
     */
    public function forceDeleted(CommentReplyLike $commentReplyLike): void
    {
        $notifications = Notification::whereType(Notification::VIDEO_COMMENT_REPLY_LIKE)
            ->whereProfileId($commentReplyLike->profile_id)
            ->whereCommentReplyId($commentReplyLike->comment_id)
            ->get();

        foreach ($notifications as $notification) {
            NotificationService::clearUnreadCount($notification->user_id);
        }

        Notification::whereType(Notification::VIDEO_COMMENT_REPLY_LIKE)
            ->whereProfileId($commentReplyLike->profile_id)
            ->whereCommentReplyId($commentReplyLike->comment_id)
            ->delete();
    }
}
