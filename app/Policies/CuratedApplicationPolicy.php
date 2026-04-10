<?php

namespace App\Policies;

use App\Models\CuratedApplication;
use App\Models\User;
use App\Services\CuratedOnboardingService;

class CuratedApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        if (! app(CuratedOnboardingService::class)->isEnabled()) {
            return false;
        }

        return $user->is_admin;
    }

    public function view(User $user, CuratedApplication $application): bool
    {
        if (! app(CuratedOnboardingService::class)->isEnabled()) {
            return false;
        }

        return $user->is_admin;
    }

    public function approve(User $user, CuratedApplication $application): bool
    {
        if (! app(CuratedOnboardingService::class)->isEnabled()) {
            return false;
        }

        return $user->is_admin && $application->isReady();
    }

    public function reject(User $user, CuratedApplication $application): bool
    {
        if (! app(CuratedOnboardingService::class)->isEnabled()) {
            return false;
        }

        return $user->is_admin && ($application->isReady() || $application->isPending());
    }

    public function delete(User $user, CuratedApplication $application): bool
    {
        if (! app(CuratedOnboardingService::class)->isEnabled()) {
            return false;
        }

        return $user->is_admin;
    }

    public function addNote(User $user, CuratedApplication $application): bool
    {
        if (! app(CuratedOnboardingService::class)->isEnabled()) {
            return false;
        }

        return $user->is_admin;
    }
}
