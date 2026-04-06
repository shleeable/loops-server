<?php

namespace App\Console\Commands;

use App\Models\AdminSetting;
use App\Models\User;
use App\Services\SettingsFileService;
use Illuminate\Console\Command;

class CheckAdminActivity extends Command
{
    protected $signature = 'admin:check-activity';

    protected $description = 'Close registration if no admin has been active in the past week';

    public function handle()
    {
        $hasActiveAdmin = User::where('is_admin', true)
            ->where('last_active_at', '>=', now()->subWeek())
            ->exists();

        if (! $hasActiveAdmin) {
            $this->closeRegistration();
            $this->warn('No admin activity in the past week. Registration closed.');
        } else {
            $this->info('Active admin found. No action needed.');
        }
    }

    protected function closeRegistration()
    {
        AdminSetting::set(
            'general.openRegistration',
            false,
            'boolean',
            true,
            null
        );

        AdminSetting::set(
            'general.registration_mode',
            'closed',
            'string',
            true,
            null
        );

        (new SettingsFileService)->flush();
    }
}
