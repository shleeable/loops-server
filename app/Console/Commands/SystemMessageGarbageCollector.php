<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\SystemMessage;
use Illuminate\Console\Command;

class SystemMessageGarbageCollector extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:system-message-garbage-collector';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete system messages that have expired';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $messages = SystemMessage::whereNotNull('expires_at')->where('is_active', true)->where('expires_at', '<', now())->get();

        foreach ($messages as $message) {
            $message->update(['is_active' => false]);
            Notification::where('system_message_id', $message->id)->delete();
        }
    }
}
