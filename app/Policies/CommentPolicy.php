<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use App\Models\Video;
use App\Services\UserFilterService;

class CommentPolicy
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
    public function view(User $user, Comment $comment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Video $video): bool
    {
        if (! $user->email_verified_at || ! $user->can_comment == 1) {
            return false;
        }

        if (
            UserFilterService::isBlocking(
                $video->profile_id,
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
    public function update(User $user, Comment $comment): bool
    {
        if ($user->is_admin) {
            return true;
        }

        if ($user->profile_id == $comment->profile_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comment $comment): bool
    {
        if (! $user->email_verified_at || ! $user->can_comment == 1) {
            return false;
        }

        if ($user->is_admin) {
            return true;
        }

        return $user->profile_id == $comment->profile_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Comment $comment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Comment $comment): bool
    {
        if (! $user->email_verified_at || ! $user->can_comment == 1) {
            return false;
        }

        if ($user->is_admin) {
            return true;
        }

        return $user->profile_id == $comment->profile_id;
    }
}
