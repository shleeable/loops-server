<?php

namespace App\Jobs;

use App\Mail\CuratedApplicationApprovedMail;
use App\Models\CuratedApplication;
use App\Services\CuratedOnboardingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessCuratedApproval implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public CuratedApplication $application
    ) {}

    public function handle(CuratedOnboardingService $service): void
    {
        if (! $this->application->isApproved()) {
            if (config('logging.dev_log')) {
                Log::warning('ProcessCuratedApproval: application is not approved', [
                    'id' => $this->application->id,
                    'status' => $this->application->status,
                ]);
            }

            return;
        }

        if ($this->application->user_id) {
            if (config('logging.dev_log')) {
                Log::info('ProcessCuratedApproval: account already created', [
                    'id' => $this->application->id,
                ]);
            }

            return;
        }

        $user = $service->createAccountForApproval($this->application);

        Mail::to($this->application->email)->send(
            new CuratedApplicationApprovedMail($this->application, $user->onboardingUrl())
        );

        if (config('logging.dev_log')) {
            Log::info('ProcessCuratedApproval: account created and welcome email sent', [
                'application_id' => $this->application->id,
                'user_id' => $user->id,
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessCuratedApproval failed', [
            'application_id' => $this->application->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
