<?php

namespace App\Console\Commands;

use App\Services\VersionCheckService;
use Illuminate\Console\Command;

class CheckVersionUpdates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'version:check
                          {--force : Force a fresh check, bypassing cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for new Loops version updates and notify admins';

    /**
     * Execute the console command.
     */
    public function handle(VersionCheckService $versionCheck): int
    {
        $this->info('🔍 Checking for Loops updates...');

        $result = $this->option('force')
            ? $versionCheck->forceCheck()
            : $versionCheck->checkForUpdates();

        if ($result['status'] === 'failure') {
            $this->error('❌ Version check failed');
            $this->line("Reason: {$result['failure_details']['reason']}");

            if ($result['error'] === 'prolonged_failure') {
                $this->warn("⚠️  Failing for {$result['failure_details']['days_failing']} days");
            }

            return self::FAILURE;
        }

        $this->info("✅ Current version: {$result['current_version']}");
        $this->info("📦 Latest version: {$result['latest_version']}");

        if ($result['update_available']) {
            $this->warn('🚀 Update available!');
            $this->line("Release: {$result['release']['name']}");
            $this->line("URL: {$result['release']['url']}");

            $notificationHistory = $versionCheck->getNotificationHistory();
            if (isset($notificationHistory['last_notified_version'])) {
                $this->info("📧 Admin notified about version {$notificationHistory['last_notified_version']}");
            }
        } else {
            $this->info('✨ You\'re running the latest version!');
        }

        return self::SUCCESS;
    }
}
