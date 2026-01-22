<?php

namespace App\Jobs\User;

use App\Models\User;
use App\Services\AccountService;
use App\Services\AccountSuggestionService;
use App\Services\AdminAuditLogService;
use App\Services\Autospam\StopForumSpamProvider;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserSpamCheckJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public $user;

    private $devLog;

    public $tries = 3;

    public $timeout = 120;

    public $deleteWhenMissingModels = true;

    public $backoff = [60, 120, 240];

    public $uniqueFor = 900;

    public function uniqueId(): string
    {
        return "user-spam-check-{$this->user->id}";
    }

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->devLog = (bool) config('logging.dev_log');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = $this->user;

        if (! $user->exists || $user->status != 1) {
            $this->devLog && Log::info('UserSpamCheckJob: User already suspended or deleted', [
                'user_id' => $user->id,
            ]);

            return;
        }

        if (empty($user->register_ip) || empty($user->email)) {
            $this->devLog && Log::warning('UserSpamCheckJob: Missing IP or email data', [
                'user_id' => $user->id,
                'has_ip' => ! empty($user->register_ip),
                'has_email' => ! empty($user->email),
            ]);

            return;
        }

        try {
            $check = app(StopForumSpamProvider::class)->checkIpAndEmail(
                $user->register_ip,
                $user->email
            );

            if ($check && $check['result'] === true) {
                $this->devLog && Log::warning('Spam detected for user', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $user->register_ip,
                    'check_data' => $check,
                ]);

                $this->suspendUser($user);
            } else {
                $this->devLog && Log::info('UserSpamCheckJob: No spam detected', [
                    'user_id' => $user->id,
                ]);
            }
        } catch (\Exception $e) {
            $this->devLog && Log::error('UserSpamCheckJob exception', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle user suspension when spam is detected
     */
    private function suspendUser(User $user): void
    {
        try {
            DB::table('oauth_access_tokens')->where('user_id', $user->id)->delete();
            DB::table('oauth_auth_codes')->where('user_id', $user->id)->delete();
            DB::table('sessions')->where('user_id', $user->id)->delete();
            $user->profile->update(['status' => 6]);
            $user->update(['status' => 6]);

            AccountService::del($user->profile_id);
            AccountSuggestionService::removeFromAll($user->profile_id);

            app(AdminAuditLogService::class)->logSpamDetectionUserSuspension($user);

            $this->devLog && Log::info('User suspended successfully', [
                'user_id' => $user->id,
                'reason' => 'spam_detected',
            ]);
        } catch (\Exception $e) {
            Log::critical('Failed to suspend spam user', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical('UserSpamCheckJob failed after all retries', [
            'user_id' => $this->user->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
