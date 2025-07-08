<?php

namespace App\Console\Commands;

use App\Models\DataExport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupExpiredExports extends Command
{
    protected $signature = 'exports:cleanup {--force : Force cleanup without confirmation}';

    protected $description = 'Clean up expired data export files';

    public function handle(): void
    {
        $expiredExports = DataExport::where('expires_at', '<', now())
            ->where('status', 'ready')
            ->get();

        if ($expiredExports->isEmpty()) {
            $this->info('No expired exports found.');

            return;
        }

        $this->info("Found {$expiredExports->count()} expired exports.");

        if (! $this->option('force') && ! $this->confirm('Do you want to proceed with cleanup?')) {
            $this->info('Cleanup cancelled.');

            return;
        }

        $cleanedCount = 0;
        $failedCount = 0;

        foreach ($expiredExports as $export) {
            try {
                if ($export->file_path && Storage::exists($export->file_path)) {
                    Storage::delete($export->file_path);
                }

                $export->update(['status' => 'expired']);
                $cleanedCount++;

                $this->line("✓ Cleaned export ID {$export->id}");
            } catch (\Exception $e) {
                $failedCount++;
                $this->error("✗ Failed to clean export ID {$export->id}: {$e->getMessage()}");
            }
        }

        $this->info("Cleanup completed: {$cleanedCount} cleaned, {$failedCount} failed.");
    }
}
