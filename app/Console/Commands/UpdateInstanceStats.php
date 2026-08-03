<?php

namespace App\Console\Commands;

use App\Jobs\Federation\DiscoverInstance;
use App\Models\Instance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class UpdateInstanceStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'instances:update-stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update instance statistics efficiently and sync missing records';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $this->info('Starting instance statistics update...');

        $this->createMissingInstances();

        return Command::SUCCESS;
    }

    /**
     * Create missing instance records from profiles table
     */
    protected function createMissingInstances()
    {
        $this->info('Discovering missing instances...');

        $batchSize = 50;

        $query = DB::table('profiles')
            ->select('domain')
            ->whereNotNull('domain')
            ->where('domain', '!=', '')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('instances')
                    ->whereColumn('instances.domain', 'profiles.domain')
                    ->where(function ($q) {
                        $q->where('is_blocked', true)
                            ->orWhere('is_silenced', true);
                    });
            })
            ->distinct();

        $missingDomains = $query->pluck('domain');

        if ($missingDomains->isEmpty()) {
            $this->info('No missing instances found.');

            return;
        }

        $total = $missingDomains->count();
        $this->info("Found {$total} missing instances. Dispatching discovery jobs in batches of {$batchSize}...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $chunks = $missingDomains->chunk($batchSize);
        $dispatchedCount = 0;

        foreach ($chunks as $chunk) {
            $jobs = $chunk->map(function ($domain) {
                return new DiscoverInstance('https://'.$domain);
            })->toArray();

            Bus::batch($jobs)
                ->allowFailures()
                ->onQueue('activitypub-in')
                ->dispatch();

            $dispatchedCount += count($jobs);
            $bar->advance(count($jobs));
        }

        $bar->finish();
        $this->newLine();
        $this->info("Dispatched {$dispatchedCount} discovery jobs in ".$chunks->count().' batches.');
    }
}
