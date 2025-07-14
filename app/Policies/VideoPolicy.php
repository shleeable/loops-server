<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Video;
use App\Services\UserFilterService;

class VideoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->is_admin) {
            return true;
        }

        if (! $user->email_verified_at) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Video $video): bool
    {
        if ($user->is_admin) {
            return true;
        }

        if (! $user->email_verified_at) {
            return false;
        }

        if ($user->profile_id == $video->profile_id) {
            return true;
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
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->email_verified_at && $user->can_upload == 1) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Video $video): bool
    {
        if ($user->is_admin) {
            return true;
        }

        if (! $user->can_upload) {
            return false;
        }

        if ($video->status != 2) {
            return false;
        }

        return $user->profile_id == $video->profile_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Video $video): bool
    {
        if ($user->is_admin) {
            return true;
        }

        if ($video->status != 2) {
            return false;
        }

        return $user->profile_id == $video->profile_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Video $video): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Video $video): bool
    {
        if ($user->is_admin) {
            return true;
        }

        if ($video->status != 2) {
            return false;
        }

        return false;
    }
}
