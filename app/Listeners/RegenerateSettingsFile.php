<?php

namespace App\Listeners;

use App\Events\SettingsUpdated;
use App\Services\SettingsFileService;
use Cache;
use Illuminate\Contracts\Queue\ShouldQueue;

class RegenerateSettingsFile implements ShouldQueue
{
    protected $settingsFileService;

    public function __construct(SettingsFileService $settingsFileService)
    {
        $this->settingsFileService = $settingsFileService;
    }

    public function handle(SettingsUpdated $event)
    {
        $this->settingsFileService->generatePublicConfig();
        $this->settingsFileService->generateAdminConfig();

        Cache::forget('settings:public');
        Cache::forget('settings:admin');
    }
}
