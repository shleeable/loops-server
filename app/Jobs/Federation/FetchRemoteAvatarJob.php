<?php

namespace App\Jobs\Federation;

use App\Models\Profile;
use App\Services\RemoteAvatarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchRemoteAvatarJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 60;

    public $uniqueFor = 3600;

    public $deleteWhenMissingModels = true;

    public function uniqueId(): string
    {
        return "fetch-remote-avatar-{$this->profile->id}";
    }

    public function __construct(
        public Profile $profile
    ) {}

    public function handle(RemoteAvatarService $service): void
    {
        $service->fetchAndStore($this->profile);
    }
}
