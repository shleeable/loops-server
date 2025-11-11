<?php

namespace App\Jobs\Federation;

use App\Models\Instance;
use App\Services\NodeinfoCrawlerService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class DiscoverInstance implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public $url;

    public $tries = 3;

    public $backoff = [60, 90, 120];

    public $timeout = 120;

    public $maxExceptions = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        $domain = parse_url($this->url, PHP_URL_HOST);

        return 'discover_instance:'.strtolower($domain);
    }

    /**
     * Determine how long the job should remain unique (in seconds).
     */
    public function uniqueFor(): int
    {
        return 3600;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $domain = parse_url($this->url, PHP_URL_HOST);

        if (! $domain) {
            if (config('logging.dev_log')) {
                Log::warning('Invalid URL provided to DiscoverInstance job', ['url' => $this->url]);
            }

            return;
        }

        $domain = strtolower($domain);

        $existingInstance = Instance::where('domain', $domain)
            ->where('version_last_checked_at', '>', now()->subHours(24))
            ->first();

        if ($existingInstance) {
            if (config('logging.dev_log')) {
                Log::info('Instance recently checked, skipping', ['domain' => $domain]);
            }

            return;
        }

        try {
            if (config('logging.dev_log')) {
                Log::info('Starting instance discovery', ['domain' => $domain]);
            }

            $versionData = app(NodeinfoCrawlerService::class)->getSoftware($domain);

            $instance = Instance::updateOrCreate([
                'domain' => $domain,
            ], [
                'software' => $versionData['name'] ?? null,
                'version' => $versionData['version'] ?? null,
                'version_last_checked_at' => now(),
            ]);

            if (config('logging.dev_log')) {
                Log::info('Instance discovery completed', [
                    'domain' => $domain,
                    'software' => $versionData['name'] ?? 'unknown',
                    'version' => $versionData['version'] ?? 'unknown',
                    'was_created' => $instance->wasRecentlyCreated,
                ]);
            }

        } catch (\Exception $e) {
            if (config('logging.dev_log')) {
                Log::error('Failed to discover instance', [
                    'domain' => $domain,
                    'attempt' => $this->attempts(),
                ]);
            }

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $domain = parse_url($this->url, PHP_URL_HOST);

        if (config('logging.dev_log')) {
            Log::error('Instance discovery job failed permanently', [
                'domain' => strtolower($domain),
                'url' => $this->url,
                'attempts' => $this->attempts(),
            ]);
        }
    }
}
