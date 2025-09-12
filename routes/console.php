<?php

use App\Console\Commands\SnapshotUsage;
use Illuminate\Support\Facades\Schedule;

Schedule::command(SnapshotUsage::class)->dailyAt('00:05')->timezone('UTC');
