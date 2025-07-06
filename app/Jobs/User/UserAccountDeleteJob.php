<?php

namespace App\Jobs\User;

use App\Models\User;
use App\Services\UserDeleteService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UserAccountDeleteJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public int $userId;

    public bool $forceDelete;

    public array $context;

    /**
     * Create a new job instance.
     *
     * @param  User  $user  The user to delete
     * @param  bool  $forceDelete  Whether to force delete (bypass soft deletes)
     * @param  array  $context  Additional context (e.g., deleted_by, reason, etc.)
     */
    public function __construct(User $user, bool $forceDelete = true, array $context = [])
    {
        $this->userId = $user->id;
        $this->forceDelete = $forceDelete;
        $this->context = array_merge([
            'deleted_at' => now()->toISOString(),
            'deleted_by' => auth()->id(),
        ], $context);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $user = User::find($this->userId);

            if (! $user) {
                if (config('logging.dev_log')) {
                    Log::warning('User account delete job: User not found', [
                        'user_id' => $this->userId,
                        'context' => $this->context,
                    ]);
                }

                return;
            }

            $result = UserDeleteService::delete($this->userId, $this->forceDelete);
        } catch (Exception $exception) {
            if (config('logging.dev_log')) {
                Log::error('User account deletion failed', [
                    'user_id' => $this->userId,
                    'error' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString(),
                    'context' => $this->context,
                ]);
            }

            throw $exception;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        if (config('logging.dev_log')) {
            Log::critical('User account delete job failed permanently', [
                'user_id' => $this->userId,
                'attempts' => $this->attempts(),
                'error' => $exception->getMessage(),
                'context' => $this->context,
            ]);
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<string>
     */
    public function tags(): array
    {
        return [
            'user-deletion',
            'user:'.$this->userId,
            $this->forceDelete ? 'force-delete' : 'soft-delete',
        ];
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(30);
    }
}
