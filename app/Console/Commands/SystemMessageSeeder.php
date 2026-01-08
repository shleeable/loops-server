<?php

namespace App\Console\Commands;

use App\Jobs\User\GenerateSystemMessageNotificationsJob;
use App\Models\SystemMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SystemMessageSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:system-message-seeder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and deliver system message updates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $messages = Storage::disk('app')->json('notifications/system.json');

        foreach ($messages as $message) {
            if (SystemMessage::where('key_id', $message['key_id'])->exists()) {
                continue;
            }
            $message['created_at'] = $message['published_at'];
            $message['notifications_generated_at'] = now();
            $msg = SystemMessage::create($message);
            GenerateSystemMessageNotificationsJob::dispatch($msg)->onQueue('notify');
        }
    }
}
