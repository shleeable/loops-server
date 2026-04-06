<?php

namespace App\Console\Commands;

use App\Services\CuratedOnboardingService;
use Illuminate\Console\Command;

class ExpireStaleCuratedApplications extends Command
{
    protected $signature = 'curated:expire-stale';

    protected $description = 'Expire pending curated applications that have exceeded the review window';

    public function handle(CuratedOnboardingService $service): int
    {
        if (! $service->isEnabled()) {
            return self::SUCCESS;
        }

        $count = $service->expireStale();

        if ($count > 0) {
            $this->info("Expired {$count} stale application(s).");
        } else {
            $this->info('No stale applications to expire.');
        }

        $expiredCount = $service->expireStaleEmailVerifications();

        if ($expiredCount > 0) {
            $this->info("Expired {$expiredCount} unverified application(s).");
        } else {
            $this->info('No unverified applications to expire.');
        }

        return self::SUCCESS;
    }
}
