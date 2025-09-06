<?php

namespace App\Console\Commands;

use App\Services\SettingsFileService;
use Illuminate\Console\Command;

class RegenerateSettingsFiles extends Command
{
    protected $signature = 'settings:regenerate
                           {--public : Only regenerate public config}
                           {--admin : Only regenerate admin config}
                           {--force : Force regeneration even if files exist}';

    protected $description = 'Regenerate settings configuration files from database';

    protected $settingsFileService;

    public function __construct(SettingsFileService $settingsFileService)
    {
        parent::__construct();
        $this->settingsFileService = $settingsFileService;
    }

    public function handle()
    {
        $publicOnly = $this->option('public');
        $adminOnly = $this->option('admin');

        if (! $publicOnly) {
            $this->info('Regenerating admin settings file...');
            $this->settingsFileService->generateAdminConfig();
            $this->info('✓ Admin settings file regenerated');
        }

        if (! $adminOnly) {
            $this->info('Regenerating public settings file...');
            $this->settingsFileService->generatePublicConfig();
            $publicUrl = $this->settingsFileService->getPublicConfigUrl();
            $this->info("✓ Public settings file regenerated: {$publicUrl}");
        }

        $this->info('Settings files regenerated successfully!');
    }
}
