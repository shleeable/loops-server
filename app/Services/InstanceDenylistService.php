<?php

namespace App\Services;

use App\Models\Instance;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class InstanceDenylistService
{
    /**
     * Column order used when the CSV has no header row.
     */
    protected const DEFAULT_COLUMNS = [
        'domain' => 0,
        'severity' => 1,
        'reject_media' => 2,
        'reject_reports' => 3,
        'public_comment' => 4,
        'obfuscate' => 5,
    ];

    public function fetch(?string $url = null): string
    {
        $url = $url ?: app('denylist_url');

        if (blank($url)) {
            throw new RuntimeException('No denylist url configured. Set DENYLIST_SYNC_URL or pass --url.');
        }

        $response = Http::withHeaders([
            'Accept' => 'text/csv, text/plain;q=0.9, */*;q=0.5',
            'User-Agent' => app('user_agent'),
        ])
            ->timeout((int) config('denylist.timeout', 30))
            ->retry((int) config('denylist.retries', 3), 250, throw: false)
            ->get($url);

        $response->throw();

        $body = $response->body();

        if (blank(trim($body))) {
            throw new RuntimeException('Denylist response was empty.');
        }

        return $body;
    }

    /**
     * @return array{entries: array<string, array>, rows: int, skipped: int}
     */
    public function parse(string $csv): array
    {
        $entries = [];
        $rows = 0;
        $skipped = 0;
        $map = null;

        foreach (preg_split('/\r\n|\r|\n/', $csv) as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $row = str_getcsv($line, ',', '"', '\\');

            if ($map === null) {
                $header = array_map(
                    fn ($column) => strtolower(trim((string) $column, " \t\"'#")),
                    $row
                );

                if (in_array('domain', $header, true)) {
                    $map = array_flip($header);

                    continue;
                }

                $map = self::DEFAULT_COLUMNS;
            }

            $rows++;

            $domain = $this->normalizeDomain($this->column($row, $map, 'domain'));

            if ($domain === null) {
                $skipped++;

                continue;
            }

            $entries[$domain] = [
                'domain' => $domain,
                'severity' => strtolower(trim((string) $this->column($row, $map, 'severity'))),
                'reject_media' => $this->toBool($this->column($row, $map, 'reject_media')),
                'reject_reports' => $this->toBool($this->column($row, $map, 'reject_reports')),
                'public_comment' => trim((string) $this->column($row, $map, 'public_comment')),
                'obfuscate' => $this->toBool($this->column($row, $map, 'obfuscate')),
            ];
        }

        return [
            'entries' => $entries,
            'rows' => $rows,
            'skipped' => $skipped,
        ];
    }

    /**
     * @return array{stats: array<string, int>, blocked: array<int, string>, missing: array<int, string>}
     */
    public function sync(array $options = []): array
    {
        $dryRun = (bool) ($options['dry_run'] ?? false);
        $create = (bool) ($options['create'] ?? config('denylist.create_missing', true));
        $matchSubdomains = (bool) ($options['match_subdomains'] ?? config('denylist.match_subdomains', false));

        $parsed = $this->parse($this->fetch($options['url'] ?? null));
        $entries = $parsed['entries'];

        $stats = [
            'rows' => $parsed['rows'],
            'entries' => count($entries),
            'unusable' => $parsed['skipped'],
            'scanned' => 0,
            'already_blocked' => 0,
            'blocked' => 0,
            'missing' => 0,
            'created' => 0,
        ];

        $blocked = [];
        $pending = [];
        $seen = [];

        Instance::select('id', 'domain', 'is_blocked')
            ->chunkById((int) config('denylist.chunk', 500), function ($instances) use (
                &$stats,
                &$blocked,
                &$pending,
                &$seen,
                $entries,
                $matchSubdomains
            ) {
                foreach ($instances as $instance) {
                    $stats['scanned']++;

                    $domain = strtolower(trim((string) $instance->domain));
                    $match = $this->match($domain, $entries, $matchSubdomains);

                    if ($match === null) {
                        continue;
                    }

                    if ($match === $domain) {
                        $seen[$domain] = true;
                    }

                    if ($instance->is_blocked) {
                        $stats['already_blocked']++;

                        continue;
                    }

                    $pending[] = $instance->id;
                    $blocked[] = $domain;
                }
            });

        $stats['blocked'] = count($pending);

        if (! $dryRun && $pending !== []) {
            foreach (array_chunk($pending, 500) as $ids) {
                Instance::whereIn('id', $ids)->update(['is_blocked' => true, 'federation_state' => 2]);
            }
        }

        $missing = array_values(array_diff(array_keys($entries), array_keys($seen)));
        $stats['missing'] = count($missing);

        if ($create) {
            if ($dryRun) {
                $stats['created'] = count($missing);
            } else {
                foreach ($missing as $domain) {
                    $instance = Instance::firstOrCreate(
                        ['domain' => $domain],
                        ['is_blocked' => true, 'federation_state' => 2]
                    );

                    if ($instance->wasRecentlyCreated) {
                        $stats['created']++;

                        continue;
                    }

                    if (! $instance->is_blocked) {
                        $instance->is_blocked = true;
                        $instance->save();

                        $stats['blocked']++;
                        $blocked[] = $domain;
                    }
                }
            }
        } else {
            $missing = [];
            $stats['created'] = 0;
        }

        if (! $dryRun) {
            app(InstanceService::class)->flushStats();
            Log::info('Instance denylist sync complete', $stats);
        }

        return [
            'stats' => $stats,
            'blocked' => $blocked,
            'missing' => $missing,
        ];
    }

    /**
     * Returns the denylist key that matched, or null.
     */
    protected function match(string $domain, array $entries, bool $matchSubdomains): ?string
    {
        if ($domain === '') {
            return null;
        }

        if (isset($entries[$domain])) {
            return $domain;
        }

        if (! $matchSubdomains) {
            return null;
        }

        $parts = explode('.', $domain);

        while (count($parts) > 2) {
            array_shift($parts);
            $parent = implode('.', $parts);

            if (isset($entries[$parent])) {
                return $parent;
            }
        }

        return null;
    }

    protected function normalizeDomain(?string $value): ?string
    {
        $domain = strtolower(trim((string) $value));

        if ($domain === '') {
            return null;
        }

        if (str_contains($domain, '://')) {
            $domain = (string) parse_url($domain, PHP_URL_HOST);
        }

        $domain = trim($domain, " \t\"'");
        $domain = ltrim($domain, '*.');
        $domain = rtrim($domain, '.');

        if ($domain === '' || str_contains($domain, '*') || ! str_contains($domain, '.')) {
            return null;
        }

        return $domain;
    }

    protected function column(array $row, array $map, string $key): ?string
    {
        $index = $map[$key] ?? null;

        if ($index === null || ! array_key_exists($index, $row)) {
            return null;
        }

        return $row[$index];
    }

    protected function toBool(?string $value): bool
    {
        return in_array(strtolower(trim((string) $value)), ['true', '1', 'yes', 't'], true);
    }
}
