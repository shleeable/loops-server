<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $video_id
 * @property int $watch_time_seconds
 * @property \Illuminate\Support\Carbon $last_watched_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $formatted_watch_time
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Video $video
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserVideoView newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserVideoView newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserVideoView query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserVideoView whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserVideoView whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserVideoView whereLastWatchedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserVideoView whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserVideoView whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserVideoView whereVideoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserVideoView whereWatchTimeSeconds($value)
 *
 * @mixin \Eloquent
 */
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
