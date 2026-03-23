<?php

namespace App\Jobs\StarterKit;

use App\Models\StarterKit;
use App\Models\StarterKitAccount;
use App\Models\StarterKitUse;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyStarterKitUsersOfNewMember implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public readonly StarterKit $starterKit,
        public readonly StarterKitAccount $newAccount,
    ) {}

    public function handle(): void
    {
        StarterKitUse::whereStarterKitId($this->starterKit->id)
            ->whereNotIn('profile_id', [$this->starterKit->profile_id, $this->newAccount->profile_id])
            ->select('profile_id')
            ->distinct()
            ->orderBy('profile_id')
            ->chunk(100, function ($uses) {
                foreach ($uses as $use) {
                    NotificationService::starterKitNewMember(
                        $use->profile_id,
                        $this->starterKit->profile_id,
                        $this->starterKit->id,
                        $this->newAccount->profile_id,
                    );
                }
            });
    }
}
