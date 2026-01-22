<?php

namespace App\Console\Commands;

use App\Services\SettingsFileService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ComposerPostInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:composer-post-install-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear specific caches after installing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Cache::forget('version_check_result');

        try {
            DB::connection()->getPdo();
            app(SettingsFileService::class)->syncDefaultSettings();
            $this->info('Settings synced successfully.');
        } catch (\Exception $e) {
            $this->warn('Database not configured yet. Skipping settings sync.');
        }
    }
}
