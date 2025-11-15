<?php

namespace App\Console\Commands;

use App\Models\Instance;
use App\Services\NodeinfoCrawlerService;
use Illuminate\Console\Command;

class InstanceNodeinfoCrawler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:instance-nodeinfo-crawler';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach (Instance::whereNull('software')->lazyById(50, column: 'id') as $instance) {
            $versionData = app(NodeinfoCrawlerService::class)->getSoftware($instance->domain);
            if (! $versionData || ! isset($versionData['name'], $versionData['version'])) {
                $instance->version_last_checked_at = now();
                $instance->save();

                continue;
            }

            $instance->software = $versionData['name'];
            $instance->version = $versionData['version'];
            $instance->version_last_checked_at = now();
            $instance->save();
        }
    }
}
