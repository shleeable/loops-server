<?php

namespace App\Jobs\User;

use App\Models\Notification;
use App\Models\SystemMessage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenerateSystemMessageNotificationsJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The system message instance.
     */
    public SystemMessage $systemMessage;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct(SystemMessage $systemMessage)
    {
        $this->systemMessage = $systemMessage;
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return 'sysmsg:'.$this->systemMessage->key_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $sm = $this->systemMessage;

        User::chunkById(250, function (Collection $users) use ($sm) {
            $notifications = $users->map(fn ($user) => [
                'type' => $sm->type,
                'user_id' => $user->profile_id,
                'system_message_id' => $sm->getKey(),
                'created_at' => $sm->published_at,
                'updated_at' => now(),
            ])->toArray();

            Notification::upsert(
                $notifications,
                ['user_id', 'system_message_id'],
                ['type', 'created_at', 'updated_at']
            );

            foreach ($users as $user) {
                Cache::forget('api:s:notify:unread:'.$user->profile_id);
                Cache::forget('api:s:notify:unread:total_counts:'.$user->profile_id);
            }
        }, column: 'id');

    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Failed to generate notifications for system message #{$this->systemMessage->id}: {$exception->getMessage()}");
    }
}
