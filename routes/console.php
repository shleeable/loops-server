<?php

use App\Console\Commands\SnapshotUsage;
use Illuminate\Support\Facades\Schedule;

Schedule::command(SnapshotUsage::class)->dailyAt('00:05')->timezone('UTC')->onOneServer();
Schedule::command('horizon:snapshot')->everyFiveMinutes()->onOneServer();

if (config('loops.admin_dashboard.autoUpdate')) {
    Schedule::command('admin:refresh-dashboard-30d')->everyThirtyMinutes()->onOneServer();
}

if (config('loops.backups.enabled')) {
    Schedule::command('backup:clean')->daily()->at('01:00')->onOneServer();
    Schedule::command('backup:run --only-db')->everySixHours()->onOneServer();
}
