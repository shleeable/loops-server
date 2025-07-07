<?php

namespace App\Jobs\Auth;

use App\Mail\EmailChangeVerification;
use App\Models\EmailChange;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailChangeVerificationDeliverJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $backoff = [30, 60, 120];

    public $timeout = 30;

    public function __construct(
        public User $user,
        public string $newEmail,
        public string $token,
        public int $emailChangeId
    ) {}

    public function uniqueId(): string
    {
        return "email_change_verification_{$this->user->id}_{$this->emailChangeId}";
    }

    public function uniqueFor(): int
    {
        return 3600;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $emailChange = EmailChange::find($this->emailChangeId);

        if (! $emailChange || $emailChange->expires_at < now()) {
            return;
        }

        try {
            Mail::to($this->newEmail)->send(new EmailChangeVerification($this->user, $this->token));
        } catch (\Exception $e) {
            if (config('logging.dev_log')) {
                Log::error('Email change verification failed to send', [
                    'user_id' => $this->user->id,
                    'new_email' => $this->newEmail,
                    'email_change_id' => $this->emailChangeId,
                    'error' => $e->getMessage(),
                ]);
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        if (config('logging.dev_log')) {
            Log::error('Email change verification job failed permanently', [
                'user_id' => $this->user->id,
                'new_email' => $this->newEmail,
                'email_change_id' => $this->emailChangeId,
                'error' => $exception->getMessage(),
            ]);
        }

        EmailChange::find($this->emailChangeId)?->delete();
    }
}
