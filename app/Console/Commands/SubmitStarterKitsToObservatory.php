<?php

namespace App\Console\Commands;

use App\Models\StarterKit;
use App\Services\StarterKitService;
use Illuminate\Console\Command;

class SubmitStarterKitsToObservatory extends Command
{
    protected $signature = 'starterkits:submit-observatory
                            {--force : Resubmit kits that have already been submitted}
                            {--limit=0 : Cap the total number processed (0 = no cap)}
                            {--kit= : Submit a single kit by id (skips all filters except local/discoverable)}
                            {--sleep=100 : Milliseconds to sleep between submissions}
                            {--dry-run : Show what would be submitted without calling the API}';

    protected $description = 'Submit local, approved, discoverable starter kits to the Loops Observatory.';

    public function handle(StarterKitService $service): int
    {
        if ($kitId = $this->option('kit')) {
            return $this->submitOne($service, (int) $kitId);
        }

        $query = StarterKit::query()
            ->where('is_local', true)
            ->where('status', 10)
            ->where('is_discoverable', true)
            ->where('approved_accounts', '>=', 1);

        if (! $this->option('force')) {
            $query->whereNull('observatory_submitted_at');
        }

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->info('No starter kits to submit.');

            return self::SUCCESS;
        }

        $limit = (int) $this->option('limit');
        if ($limit > 0 && $limit < $total) {
            $total = $limit;
        }

        $this->info("Submitting {$total} starter kit(s) to the Observatory…");

        $sleepMs = max(0, (int) $this->option('sleep'));
        $dryRun = (bool) $this->option('dry-run');
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $submitted = 0;
        $failed = 0;
        $processed = 0;

        $query->orderBy('id')->chunkById(100, function ($kits) use (
            $service, $bar, $dryRun, $sleepMs, $limit, &$submitted, &$failed, &$processed
        ) {
            foreach ($kits as $kit) {
                if ($limit > 0 && $processed >= $limit) {
                    return false;
                }

                if ($dryRun) {
                    $bar->advance();
                    $processed++;

                    continue;
                }

                $ok = $service->submitToObservatory($kit);
                $ok ? $submitted++ : $failed++;
                $processed++;
                $bar->advance();

                if ($sleepMs > 0) {
                    usleep($sleepMs * 1000);
                }
            }

            return true;
        });

        $bar->finish();
        $this->newLine(2);

        if ($dryRun) {
            $this->info("Dry run: {$processed} kit(s) would have been submitted.");

            return self::SUCCESS;
        }

        $this->info("Submitted: {$submitted}");
        if ($failed > 0) {
            $this->warn("Failed: {$failed} (see logs for details)");
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function submitOne(StarterKitService $service, int $kitId): int
    {
        $kit = StarterKit::find($kitId);

        if (! $kit) {
            $this->error("Kit {$kitId} not found.");

            return self::FAILURE;
        }

        if (! $kit->is_local) {
            $this->error("Kit {$kitId} is not a local kit.");

            return self::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->info("Dry run: would submit kit {$kit->id} ({$kit->title}) to Observatory.");

            return self::SUCCESS;
        }

        $ok = $service->submitToObservatory($kit);

        if ($ok) {
            $this->info("Submitted kit {$kit->id} ({$kit->title}).");

            return self::SUCCESS;
        }

        $this->error("Failed to submit kit {$kit->id}. Check `is_discoverable`, `status`, permalink, and the application log.");

        return self::FAILURE;
    }
}
