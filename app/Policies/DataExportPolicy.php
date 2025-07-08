<?php

namespace App\Policies;

use App\Models\DataExport;
use App\Models\User;

class DataExportPolicy
{
    public function view(User $user, DataExport $export): bool
    {
        return $user->id === $export->user_id;
    }

    public function download(User $user, DataExport $export): bool
    {
        return $user->id === $export->user_id
            && $export->status === 'ready'
            && ! $export->isExpired();
    }

    public function delete(User $user, DataExport $export): bool
    {
        return $user->id === $export->user_id;
    }
}
