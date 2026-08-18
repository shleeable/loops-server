<?php

namespace App\Console\Commands;

use App\Services\InstanceDenylistService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('instances:sync-denylist
                            {--url= : Override the configured denylist url}
                            {--dry-run : Report what would change without writing}
                            {--no-create : Do not create rows for unknown listed domains}
                            {--subdomains : Also block subdomains of listed domains}
                            {--show= : Number of affected domains to print (default 25)}')]
#[Description('Fetch a remote CSV denylist and block matching instances')]
class InstanceDenylistSyncCommand extends Command
{
    public function handle(InstanceDenylistService $service): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info($dryRun ? 'Running denylist sync in dry run mode' : 'Running denylist sync');

        try {
            $result = $service->sync([
                'url' => $this->option('url'),
                'dry_run' => $dryRun,
                'create' => ! $this->option('no-create'),
                'match_subdomains' => (bool) $this->option('subdomains'),
            ]);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $stats = $result['stats'];

        $this->newLine();
        $this->table(['Metric', 'Count'], [
            ['CSV rows read', $stats['rows']],
            ['Usable domains', $stats['entries']],
            ['Unusable rows (obfuscated or malformed)', $stats['unusable']],
            ['Instances scanned', $stats['scanned']],
            ['Already blocked', $stats['already_blocked']],
            [$dryRun ? 'Would block' : 'Newly blocked', $stats['blocked']],
            ['Listed but not in instances table', $stats['missing']],
            [$dryRun ? 'Would create' : 'Rows created', $stats['created']],
        ]);

        $show = (int) ($this->option('show') ?? 25);

        if ($show > 0) {
            $this->preview($dryRun ? 'Would block' : 'Blocked', $result['blocked'], $show);
            $this->preview($dryRun ? 'Would create' : 'Created', $result['missing'], $show);
        }

        $this->newLine();
        $this->info('Done.');

        return self::SUCCESS;
    }

    protected function preview(string $label, array $domains, int $show): void
    {
        if ($domains === []) {
            return;
        }

        $this->newLine();
        $this->line("<comment>{$label}:</comment>");

        foreach (array_slice($domains, 0, $show) as $domain) {
            $this->line("  {$domain}");
        }

        $remaining = count($domains) - $show;

        if ($remaining > 0) {
            $this->line("  ... and {$remaining} more");
        }
    }
}
