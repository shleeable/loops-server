<?php

namespace App\Console\Commands;

use App\Models\Instance;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstanceApiCrawler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:instance-api-crawler {--limit=50 : Number of instances to process} {--timeout=10 : Request timeout in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl instance API endpoints to fetch and store description data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $timeout = $this->option('timeout');

        $this->info('Starting instance API crawler...');
        $this->info("Processing up to {$limit} instances with {$timeout}s timeout");

        $processed = 0;
        $updated = 0;
        $errors = 0;

        $query = Instance::where(function ($q) {
            $q->whereNull('instance_last_crawled_at')
                ->orWhere('instance_last_crawled_at', '<', now()->subWeek());
        });

        foreach ($query->lazyById((int) $limit, 'id') as $instance) {
            $processed++;

            $this->line("Processing: {$instance->domain}");

            try {
                $result = $this->crawlInstanceApi($instance, (int) $timeout);
                if ($result['updated']) {
                    $updated++;
                    $this->info("✓ Updated {$instance->domain} (using: {$result['source']})");
                } else {
                    $this->comment("- Skipped {$instance->domain} (no valid description)");
                }
            } catch (Exception $e) {
                $errors++;
                $instance->instance_last_crawled_at = now();
                $instance->save();
                $this->error("✗ Error processing {$instance->domain}: ".$e->getMessage());
                Log::warning('Instance API crawler error', [
                    'domain' => $instance->domain,
                    'error' => $e->getMessage(),
                ]);
            }

            usleep(500000);
        }

        $this->newLine();
        $this->info('Crawling completed!');
        $this->table(['Metric', 'Count'], [
            ['Processed', $processed],
            ['Updated', $updated],
            ['Errors', $errors],
        ]);
    }

    /**
     * Crawl a single instance API endpoint
     *
     * @return array ['updated' => bool, 'source' => string|null]
     */
    private function crawlInstanceApi(Instance $instance, int $timeout): array
    {
        $url = "https://{$instance->domain}/api/v1/instance";

        try {
            $response = Http::timeout($timeout)
                ->retry(2, 1000)
                ->get($url);

            if (! $response->successful()) {
                Log::info('Instance API not available', [
                    'domain' => $instance->domain,
                    'status' => $response->status(),
                ]);

                return ['updated' => false, 'source' => null];
            }

            $data = $response->json();

            if ($data === null || $data === false) {
                Log::warning('Failed to parse JSON response from instance API', [
                    'domain' => $instance->domain,
                    'response_body' => substr($response->body(), 0, 200),
                ]);

                return ['updated' => false, 'source' => null];
            }

            if (! is_array($data)) {
                Log::warning('Invalid JSON structure from instance API', [
                    'domain' => $instance->domain,
                    'data_type' => gettype($data),
                ]);

                return ['updated' => false, 'source' => null];
            }

            $description = null;
            $source = null;

            if (isset($data['description']) &&
                is_string($data['description']) &&
                trim($data['description']) !== '') {
                $description = trim($data['description']);
                $source = 'description';
            } elseif (isset($data['short_description']) &&
                     is_string($data['short_description']) &&
                     trim($data['short_description']) !== '') {
                $description = trim($data['short_description']);
                $source = 'short_description';
            }

            if ($description === null) {
                Log::info('No valid description or short_description found', [
                    'domain' => $instance->domain,
                ]);

                return ['updated' => false, 'source' => null];
            }

            if ($instance->description !== $description) {
                $instance->update([
                    'description' => $description,
                    'instance_last_crawled_at' => now(),
                ]);

                return ['updated' => true, 'source' => $source];
            }

            return ['updated' => false, 'source' => null];

        } catch (Exception $e) {
            Log::error('Failed to crawl instance API', [
                'domain' => $instance->domain,
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
