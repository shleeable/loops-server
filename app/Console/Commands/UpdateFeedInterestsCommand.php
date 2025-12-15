<?php

namespace App\Console\Commands;

use App\Jobs\UpdateUserInterestsJob;
use App\Models\Profile;
use Illuminate\Console\Command;

class UpdateFeedInterestsCommand extends Command
{
    protected $signature = 'feed:update-interests 
                          {--profile= : Specific profile ID to update}
                          {--active-days=7 : Only update profiles active in last N days}';

    protected $description = 'Update user interests for personalized feed recommendations';

    public function handle(): int
    {
        if ($profileId = $this->option('profile')) {
            $this->info("Updating interests for profile {$profileId}...");
            UpdateUserInterestsJob::dispatch((int) $profileId);

            return 0;
        }

        $activeDays = (int) $this->option('active-days') ?: 7;

        $profiles = Profile::whereHas('user', function ($query) use ($activeDays) {
            $query->where('last_active_at', '>=', now()->subDays($activeDays));
        })->pluck('id');

        $this->info("Queuing interest updates for {$profiles->count()} active profiles...");

        $bar = $this->output->createProgressBar($profiles->count());
        $bar->start();

        foreach ($profiles as $profileId) {
            UpdateUserInterestsJob::dispatch((int) $profileId);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Done! Jobs queued for processing.');

        return 0;
    }
}
