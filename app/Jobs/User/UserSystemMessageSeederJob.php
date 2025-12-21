<?php

namespace App\Jobs\User;

use App\Models\Notification;
use App\Models\Profile;
use App\Models\SystemMessage;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class UserSystemMessageSeederJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The system message instance.
     */
    public Profile $profile;

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
    public function __construct(Profile $profile)
    {
        $this->profile = $profile;
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return 'user:sysmsgseeding:'.$this->profile->id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $profile = $this->profile->fresh();

        if (! $profile) {
            return;
        }

        if ($profile->domain || ! $profile->user_id) {
            return;
        }

        $messages = SystemMessage::active()->published()->orderBy('id')->get();

        $notifications = $messages->map(fn ($message) => [
            'type' => $message->type,
            'user_id' => $profile->id,
            'system_message_id' => $message->getKey(),
            'created_at' => $message->published_at,
            'updated_at' => now(),
        ])->toArray();

        if (! empty($notifications)) {
            Notification::upsert(
                $notifications,
                ['user_id', 'system_message_id'],
                ['type', 'created_at', 'updated_at']
            );
        }

        Cache::forget('api:s:notify:unread:'.$profile->id);
        Cache::forget('api:s:notify:unread:total_counts:'.$profile->id);
    }
}
