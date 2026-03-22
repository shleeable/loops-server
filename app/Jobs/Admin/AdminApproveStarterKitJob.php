<?php

namespace App\Jobs\Admin;

use App\Models\StarterKit;
use App\Models\StarterKitAccount;
use App\Services\AdminDashboardService;
use App\Services\NotificationService;
use App\Services\StarterKitService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AdminApproveStarterKitJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $starterKit;

    /**
     * Create a new job instance.
     */
    public function __construct(StarterKit $starterKit)
    {
        $this->starterKit = $starterKit;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $starterKit = $this->starterKit->fresh();

        $accounts = $starterKit->starterKitAccounts;

        foreach ($accounts as $account) {
            if ($account->kit_status === StarterKitAccount::STATUS_APPROVED_PENDING_ADMIN_REVIEW) {
                $account->kit_status = 1;
                $account->approved_at = now();
                $account->save();
            } else {
                NotificationService::starterKitAddAccount($account->profile_id, $starterKit->profile_id, $starterKit->id);
            }
        }

        $starterKit->adminApprove();
        $starterKit->syncAccountCount();
        $starterKit->update(['status' => 10]);
        app(StarterKitService::class)->forget($starterKit->id);
        app(AdminDashboardService::class)->getReportsCount(true);
    }
}
