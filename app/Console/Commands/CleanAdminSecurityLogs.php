<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanAdminSecurityLogs extends Command
{
    protected $signature = 'logs:clean-admin-security {--days=14 : Number of days to keep}';

    protected $description = 'Clean old admin security logs';

    public function handle()
    {
        $days = (int) $this->option('days');
        $path = storage_path('logs');
        $cutoffDate = Carbon::now()->subDays($days);

        $deleted = 0;

        foreach (File::glob("{$path}/admin-security-*.log") as $file) {
            $fileDate = Carbon::createFromTimestamp(File::lastModified($file));

            if ($fileDate->lt($cutoffDate)) {
                File::delete($file);
                $deleted++;
                $this->info('Deleted: '.basename($file));
            }
        }

        $this->info("Cleaned {$deleted} old admin security log file(s)");

        return Command::SUCCESS;
    }
}
