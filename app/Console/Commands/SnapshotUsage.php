<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SnapshotUsage extends Command
{
    protected $signature = 'usage:snapshot';

    protected $description = 'Compute daily 30d/180d active users';

    public function handle(): int
    {
        $today = now('UTC')->toDateString();

        $active30 = DB::table('user_daily_actives')
            ->where('day', '>=', now('UTC')->subDays(30)->toDateString())
            ->distinct('user_id')->count('user_id');

        $active180 = DB::table('user_daily_actives')
            ->where('day', '>=', now('UTC')->subDays(180)->toDateString())
            ->distinct('user_id')->count('user_id');

        DB::table('usage_snapshots')->updateOrInsert(
            ['day' => $today],
            ['active_30d' => $active30, 'active_180d' => $active180, 'updated_at' => now(), 'created_at' => now()]
        );

        $this->info("Snapshot for {$today}: 30d={$active30}, 180d={$active180}");

        return self::SUCCESS;
    }
}
