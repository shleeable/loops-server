<?php

namespace App\Observers;

use App\Models\CommentLike;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class CommentLikeObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the CommentLike "created" event.
     */
    public function created(CommentLike $commentLike): void
    {
        $comment = $commentLike->comment;

        if ($comment && $comment->ap_id == null && $comment->profile_id != $commentLike->profile_id) {
            NotificationService::newCommentLike(
                $comment->profile_id,
                $comment->id,
                $commentLike->profile_id,
                $comment->video_id
            );
        }
    }

    /**
     * Handle the CommentLike "updated" event.
     */
    public function updated(CommentLike $commentLike): void
    {
        //
    }

    /**
     * Handle the CommentLike "deleted" event.
     */
    public function deleted(CommentLike $commentLike): void
    {
        $notifications = Notification::whereType(Notification::VIDEO_COMMENT_LIKE)
            ->whereProfileId($commentLike->profile_id)
            ->whereCommentId($commentLike->comment_id)
            ->get();

        foreach ($notifications as $notification) {
            NotificationService::clearUnreadCount($notification->user_id);
        }

        Notification::whereType(Notification::VIDEO_COMMENT_LIKE)
            ->whereProfileId($commentLike->profile_id)
            ->whereCommentId($commentLike->comment_id)
            ->delete();
    }

    /**
     * Handle the CommentLike "restored" event.
     */
    public function restored(CommentLike $commentLike): void
    {
        //
    }

    /**
     * Handle the CommentLike "force deleted" event.
     */
    public function forceDeleted(CommentLike $commentLike): void
    {
        $notifications = Notification::whereType(Notification::VIDEO_COMMENT_LIKE)
            ->whereProfileId($commentLike->profile_id)
            ->whereCommentId($commentLike->comment_id)
            ->get();

        foreach ($notifications as $notification) {
            NotificationService::clearUnreadCount($notification->user_id);
        }

        Notification::whereType(Notification::VIDEO_COMMENT_LIKE)
            ->whereProfileId($commentLike->profile_id)
            ->whereCommentId($commentLike->comment_id)
            ->delete();
    }
}
