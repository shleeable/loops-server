<?php

namespace App\Policies;

use App\Models\Playlist;
use App\Models\User;
use App\Services\FollowerService;
use App\Services\PlaylistService;

class PlaylistPolicy
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
    public function view(User $user, Playlist $playlist): bool
    {
        if ($user->is_admin || $user->profile_id == $playlist->profile_id) {
            return true;
        }

        if ($playlist->visibility === 'public' || $playlist->visibility === 'unlisted') {
            return true;
        }

        if ($playlist->visibility === 'followers') {
            if (FollowerService::follows($user->profile_id, $playlist->profile_id)) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->is_admin) {
            return true;
        }

        if (! $user->can_playlist) {
            return false;
        }

        $profile = $user->profile;

        if (PlaylistService::canCreatePlaylist($profile)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Playlist $playlist): bool
    {
        return (int) $user->profile_id === (int) $playlist->profile_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Playlist $playlist): bool
    {
        return $user->profile_id === $playlist->profile_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Playlist $playlist): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Playlist $playlist): bool
    {
        return false;
    }
}
