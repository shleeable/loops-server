<?php

namespace App\Console\Commands;

use App\Models\Instance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FetchNodeinfoCommand extends Command
{
    protected $signature = 'nodeinfo:fetch {domain} {--software= : Optional software type hint}';

    protected $description = 'Fetch nodeinfo directly for a domain and update Instance model';

    /**
     * Map of software => nodeinfo paths to try
     */
    protected array $softwarePaths = [
        'pixelfed' => [
            '/api/nodeinfo/2.0.json',
        ],
        'mastodon' => [
            '/nodeinfo/2.0',
        ],
        'pleroma' => [
            '/nodeinfo/2.0.json',
        ],
        'misskey' => [
            '/nodeinfo/2.1',
            '/nodeinfo/2.0',
        ],
        'lemmy' => [
            '/nodeinfo/2.1',
        ],
        'loops' => [
            '/nodeinfo/2.0',
            '/nodeinfo/2.1',
        ],
        'peertube' => [
            '/nodeinfo/2.0.json',
        ],
        'wordpress' => [
            '/wp-json/activitypub/1.0/nodeinfo/2.0',
        ],
        'writefreely' => [
            '/api/nodeinfo',
        ],
    ];

    /**
     * Standard nodeinfo paths as fallback
     */
    protected array $standardPaths = [
        '/nodeinfo/2.1',
        '/nodeinfo/2.0',
        '/nodeinfo/2.1.json',
        '/nodeinfo/2.0.json',
    ];

    public function handle()
    {
        $domain = $this->argument('domain');
        $softwareHint = $this->option('software');

        $domain = $this->cleanDomain($domain);

        $this->info("Fetching nodeinfo for: {$domain}");

        if ($softwareHint) {
            $this->line("Using software hint: {$softwareHint}");
        }

        $nodeInfoData = $this->fetchNodeinfo($domain, $softwareHint);

        if (! $nodeInfoData) {
            $this->error("Failed to fetch nodeinfo for {$domain}");

            return 1;
        }

        $software = $nodeInfoData['software']['name'] ?? null;
        $version = $nodeInfoData['software']['version'] ?? 'unknown';

        if (! $software) {
            $this->error('No software information found in nodeinfo');

            return 1;
        }

        $this->info("Found software: {$software} (version: {$version})");

        $instance = Instance::firstOrNew(['domain' => $domain]);
        $instance->software = $software;
        $instance->version = $version;
        $instance->version_last_checked_at = now();
        $instance->save();

        $this->info('✓ Instance updated successfully');

        return 0;
    }

    protected function cleanDomain(string $input): string
    {
        $trimmed = trim($input);

        if (Str::startsWith($trimmed, ['http://', 'https://'])) {
            $parts = parse_url($trimmed);

            return $parts['host'] ?? $trimmed;
        }

        return $trimmed;
    }

    protected function fetchNodeinfo(string $domain, ?string $softwareHint = null): ?array
    {
        $scheme = 'https';
        $pathsToTry = [];

        if ($softwareHint && isset($this->softwarePaths[strtolower($softwareHint)])) {
            $pathsToTry = $this->softwarePaths[strtolower($softwareHint)];
            $this->line("Trying software-specific paths for: {$softwareHint}");
        } else {
            $pathsToTry = $this->standardPaths;
        }

        foreach ($pathsToTry as $path) {
            $url = "{$scheme}://{$domain}{$path}";

            $this->line("  → {$url}");

            try {
                $response = Http::withHeaders([
                    'User-Agent' => app('user_agent'),
                    'Accept' => 'application/json',
                ])
                    ->timeout(8)
                    ->withOptions([
                        'allow_redirects' => true,
                    ])
                    ->get($url);

                if ($response->ok()) {
                    $json = $response->json();

                    if (is_array($json) && isset($json['software'])) {
                        $this->info("  ✓ Found nodeinfo at: {$path}");

                        return $json;
                    }
                }
            } catch (\Throwable $e) {
            }
        }

        if ($softwareHint && $pathsToTry !== $this->standardPaths) {
            $this->line('Software-specific paths failed, trying standard paths...');

            foreach ($this->standardPaths as $path) {
                $url = "{$scheme}://{$domain}{$path}";

                $this->line("  → {$url}");

                try {
                    $response = Http::withHeaders([
                        'User-Agent' => app('user_agent'),
                        'Accept' => 'application/json',
                    ])
                        ->timeout(8)
                        ->withOptions([
                            'allow_redirects' => true,
                        ])
                        ->get($url);

                    if ($response->ok()) {
                        $json = $response->json();

                        if (is_array($json) && isset($json['software'])) {
                            $this->info("  ✓ Found nodeinfo at: {$path}");

                            return $json;
                        }
                    }
                } catch (\Throwable $e) {
                }
            }
        }

        return null;
    }
}
