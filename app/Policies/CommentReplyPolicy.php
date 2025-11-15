<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\CommentReply;
use App\Models\User;
use App\Services\UserFilterService;

class CommentReplyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CommentReply $commentReply): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Comment $comment): bool
    {
        if (! $user->email_verified_at || ! $user->can_comment == 1) {
            return false;
        }

        if (
            UserFilterService::isBlocking(
                $comment->profile_id,
                $user->profile_id
            )
        ) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CommentReply $commentReply): bool
    {
        if ($user->is_admin) {
            return true;
        }

        if ($user->profile_id == $commentReply->profile_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CommentReply $commentReply): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CommentReply $commentReply): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CommentReply $commentReply): bool
    {
        return false;
    }
}
