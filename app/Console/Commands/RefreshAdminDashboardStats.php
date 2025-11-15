<?php

namespace App\Console\Commands;

use App\Services\AdminDashboardService;
use Illuminate\Console\Command;

class RefreshAdminDashboardStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Run as: php artisan admin:refresh-dashboard-30d
     */
    protected $signature = 'admin:refresh-dashboard-30d';

    /**
     * The console command description.
     */
    protected $description = 'Warm the admin dashboard cache for the last 30 days';

    public function __construct(protected AdminDashboardService $dashboardService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $data = $this->dashboardService->warmThirtyDayDashboard();

        $this->info('Admin dashboard cache refreshed for 30d period.');
        if (isset($data['cached_at'])) {
            $this->line('Cached at: '.$data['cached_at']);
        }

        return Command::SUCCESS;
    }
}
