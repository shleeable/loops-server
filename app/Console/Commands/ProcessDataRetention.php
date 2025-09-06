<?php

namespace App\Console\Commands;

use App\Models\UserDataSettings;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessDataRetention extends Command
{
    protected $signature = 'data:process-retention {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Process data retention policies and clean up old data';

    public function handle(): void
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('DRY RUN MODE - No data will actually be deleted');
        }

        $settings = UserDataSettings::whereNotNull('data_retention_period')
            ->where('data_retention_period', '!=', 'never')
            ->with('user')
            ->get();

        $totalProcessed = 0;

        foreach ($settings as $setting) {
            $retentionMonths = $setting->getRetentionInMonths();
            if (! $retentionMonths) {
                continue;
            }

            $cutoffDate = Carbon::now()->subMonths($retentionMonths);
            $user = $setting->user;

            $oldVideos = $user->videos()->where('created_at', '<', $cutoffDate)->count();
            $oldComments = $user->comments()->where('created_at', '<', $cutoffDate)->count();
            $oldViews = $user->videoViews()->where('created_at', '<', $cutoffDate)->count();

            if ($oldVideos + $oldComments + $oldViews > 0) {
                $this->info("User {$user->username}: {$oldVideos} videos, {$oldComments} comments, {$oldViews} views to delete");

                if (! $isDryRun) {
                    $user->videos()->where('created_at', '<', $cutoffDate)->delete();
                    $user->comments()->where('created_at', '<', $cutoffDate)->delete();
                    $user->videoViews()->where('created_at', '<', $cutoffDate)->delete();
                }

                $totalProcessed++;
            }
        }

        $action = $isDryRun ? 'would be processed' : 'processed';
        $this->info("Data retention {$action} for {$totalProcessed} users.");
    }
}
