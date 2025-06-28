<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Video;
use App\Models\VideoLike;
use App\Services\UserFilterService;

class VideoLikePolicy
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
    public function view(User $user, VideoLike $videoLike): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Video $video): bool
    {
        if (! $user->email_verified_at || $user->can_like != 1) {
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
    public function update(User $user, VideoLike $videoLike): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, VideoLike $videoLike): bool
    {
        if (! $user->email_verified_at || $user->can_like != 1) {
            return false;
        }

        if ($user->is_admin) {
            return true;
        }

        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, VideoLike $videoLike): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, VideoLike $videoLike): bool
    {
        if ($user->is_admin) {
            return true;
        }

        if ($user->email_verified_at && $user->can_like == 1) {
            return true;
        }

        return false;
    }
}
