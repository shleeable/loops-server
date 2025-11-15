<?php

namespace App\Observers;

use App\Models\Comment;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class CommentObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the Comment "created" event.
     */
    public function created(Comment $comment): void
    {
        $video = $comment->video;
        if ($video->is_local && $comment->profile_id != $video->profile_id) {
            NotificationService::newVideoComment(
                $comment->profile_id,
                $video->profile_id,
                $video->id,
                $comment->id
            );
        }
    }

    /**
     * Handle the Comment "updated" event.
     */
    public function updated(Comment $comment): void
    {
        //
    }

    /**
     * Handle the Comment "deleted" event.
     */
    public function deleted(Comment $comment): void
    {
        $notifications = Notification::whereType(Notification::NEW_VIDCOMMENT)
            ->whereProfileId($comment->profile_id)
            ->whereCommentId($comment->id)
            ->get();

        foreach ($notifications as $notification) {
            NotificationService::clearUnreadCount($notification->user_id);
        }

        Notification::whereType(Notification::NEW_VIDCOMMENT)
            ->whereProfileId($comment->profile_id)
            ->whereCommentId($comment->id)
            ->delete();
    }

    /**
     * Handle the Comment "restored" event.
     */
    public function restored(Comment $comment): void
    {
        //
    }

    /**
     * Handle the Comment "force deleted" event.
     */
    public function forceDeleted(Comment $comment): void
    {
        $notifications = Notification::whereType(Notification::NEW_VIDCOMMENT)
            ->whereProfileId($comment->profile_id)
            ->whereCommentId($comment->id)
            ->get();

        foreach ($notifications as $notification) {
            NotificationService::clearUnreadCount($notification->user_id);
        }

        Notification::whereType(Notification::NEW_VIDCOMMENT)
            ->whereProfileId($comment->profile_id)
            ->whereCommentId($comment->id)
            ->delete();
    }
}
