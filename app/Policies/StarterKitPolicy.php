<?php

namespace App\Policies;

use App\Models\StarterKit;
use App\Models\User;
use App\Services\AccountService;
use App\Services\StarterKitService;

class StarterKitPolicy
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
    public function view(?User $user, StarterKit $starterKit): bool
    {
        if ($starterKit->status == 10) {
            return true;
        }

        if ($user?->profile_id === $starterKit->profile_id || $user?->is_admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->status != 1) {
            return false;
        }

        if ($user->can_create_starter_kits != true) {
            return false;
        }

        if ($user->is_admin) {
            return true;
        }

        $config = app(StarterKitService::class)->getConfig();
        $maxKits = data_get($config, 'max_kits_allowed', 10);
        $minFollowers = data_get($config, 'min_followers_required');
        $totalKits = StarterKit::whereProfileId($user->profile_id)->count();

        $canCreate = $maxKits > $totalKits;

        if ($minFollowers && $minFollowers > 0) {
            $acct = app(AccountService::class)->get($user->profile_id);
            if ($acct['follower_count'] < $minFollowers) {
                $canCreate = false;
            }
        }

        return $canCreate;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, StarterKit $starterKit): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StarterKit $starterKit): bool
    {
        if ($user->profile_id === $starterKit->profile_id || $user->is_admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, StarterKit $starterKit): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, StarterKit $starterKit): bool
    {
        return false;
    }
}
