<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AdminSetting;
use App\Services\SettingsFileService;
use function Laravel\Prompts\text;

class AdminSetAppUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:admin-set-app-url';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the application URL for the admin settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $domain = text(
            label: 'What is your domain?',
            placeholder: 'example.com',
            hint: 'Enter your domain without https://',
            validate: fn (string $value) => match (true) {
                strlen($value) < 3 => 'Domain must be at least 3 characters.',
                !filter_var('https://' . $value, FILTER_VALIDATE_URL) => 'Please enter a valid domain.',
                default => null
            }
        );

        $updatedUrl = 'https://' . $domain;

        AdminSetting::set(
            key: 'general.instanceUrl',
            value: $updatedUrl,
            type: 'string',
            isPublic: true,
            description: 'The main application URL'
        );

        $this->info("App URL successfully set to: {$updatedUrl}");
    }
}
