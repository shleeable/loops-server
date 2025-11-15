<?php

namespace App\Services;

use App\Models\Profile;

class ProfileDeleteService
{
    public function handleRemoteProfileDelete(Profile $profile)
    {
        if ($profile->local) {
            return;
        }
    }
}
