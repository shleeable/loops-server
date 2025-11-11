<?php

namespace App\Jobs\Auth;

use App\Mail\NewAccountEmailVerify;
use App\Models\UserRegisterVerify;
use Exception;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NewAccountEmailVerifyJob implements ShouldBeUnique, ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $verify;

    public $tries = 3;

    public $maxExceptions = 2;

    public $timeout = 120;

    public $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
     */
    public function __construct(UserRegisterVerify $verify)
    {
        $this->verify = $verify;

        $this->onQueue('email-verification');

        $this->delay(now()->addSeconds(2));
    }

    /**
     * Get the unique ID for the job to prevent duplicates
     */
    public function uniqueId(): string
    {
        return "email-verify-{$this->verify->id}-{$this->verify->email}";
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [1, 10, 15];
    }

    /**
     * How long the unique lock should be maintained
     */
    public function uniqueFor(): int
    {
        return 3600;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (! $this->shouldSendEmail()) {
                if (config('logging.dev_log')) {
                    Log::info('Skipping email verification - no longer needed', [
                        'verify_id' => $this->verify->id,
                        'email' => $this->verify->email,
                    ]);
                }

                return;
            }

            Mail::to($this->verify->email)->send(new NewAccountEmailVerify($this->verify));

            if (config('logging.dev_log')) {
                Log::info('Email verification sent successfully', [
                    'verify_id' => $this->verify->id,
                    'email' => $this->verify->email,
                    'code' => $this->verify->verify_code,
                ]);
            }

            $this->verify->update(['email_last_sent_at' => now()]);

        } catch (Exception $e) {
            if (config('logging.dev_log')) {
                Log::error('Failed to send email verification', [
                    'verify_id' => $this->verify->id,
                    'email' => $this->verify->email,
                    'error' => $e->getMessage(),
                    'attempt' => $this->attempts(),
                ]);
            }

            $this->verify->increment('resend_attempts');

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('Email verification job failed permanently', [
            'verify_id' => $this->verify->id,
            'email' => $this->verify->email,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }

    /**
     * Check if we should still send the email
     */
    private function shouldSendEmail(): bool
    {
        $this->verify->refresh();

        if ($this->verify->verified_at) {
            return false;
        }

        if ($this->verify->resend_attempts > 5) {
            return false;
        }

        if ($this->verify->email_last_sent_at &&
            $this->verify->email_last_sent_at->diffInMinutes(now()) > 5) {
            return false;
        }

        return true;
    }
}
