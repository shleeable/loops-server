<?php

namespace App\Jobs\Federation;

use App\Models\Instance;
use App\Services\NodeinfoCrawlerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FetchInstanceNodeinfo implements ShouldQueue
{
    use Queueable;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 60;

    public $instance;

    public $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
     */
    public function __construct(Instance $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $instance = $this->instance;

        $versionData = app(NodeinfoCrawlerService::class)->getSoftware($instance->domain);

        if (! $versionData || ! isset($versionData['name'], $versionData['version'])) {
            $instance->version_last_checked_at = now();
            $instance->save();
        } else {
            $instance->software = $versionData['name'];
            $instance->version = $versionData['version'];
            $instance->version_last_checked_at = now();
            $instance->save();
        }
    }
}
