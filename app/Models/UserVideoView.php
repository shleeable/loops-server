<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserVideoView extends Model
{
    protected $fillable = [
        'user_id',
        'video_id',
        'watch_time_seconds',
        'last_watched_at',
    ];

    protected $casts = [
        'last_watched_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function getFormattedWatchTimeAttribute(): string
    {
        $hours = floor($this->watch_time_seconds / 3600);
        $minutes = floor(($this->watch_time_seconds % 3600) / 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }
}
