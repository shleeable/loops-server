<?php

namespace App\Listeners;

use App\Events\SettingsUpdated;
use App\Services\SettingsFileService;
use Illuminate\Contracts\Queue\ShouldQueue;

class RegenerateSettingsFile implements ShouldQueue
{
    public function __construct() {}

    public function handle(SettingsUpdated $event)
    {
        (new SettingsFileService)->generatePublicConfig();
        (new SettingsFileService)->generateAdminConfig();
    }
}
