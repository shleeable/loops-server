<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedImpression extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'profile_id',
        'video_id',
        'viewed_at',
        'watch_duration',
        'completed',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
        'completed' => 'boolean',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }
}
