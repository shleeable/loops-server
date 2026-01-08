<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedFeedback extends Model
{
    public $timestamps = false;

    protected $table = 'feed_feedback';

    protected $fillable = [
        'profile_id',
        'video_id',
        'feedback_type',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    const TYPE_LIKE = 'like';

    const TYPE_DISLIKE = 'dislike';

    const TYPE_NOT_INTERESTED = 'not_interested';

    const TYPE_HIDE_CREATOR = 'hide_creator';

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }
}
