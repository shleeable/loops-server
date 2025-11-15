<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $activity_id
 * @property string $inbox_url
 * @property int $profile_id
 * @property int $attempts
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $last_attempt_at
 * @property \Illuminate\Support\Carbon|null $next_retry_at
 * @property string|null $error_message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Models\Activity $activity
 * @property-read \App\Models\Profile $profile
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityDelivery due($asOf = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityDelivery newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityDelivery newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityDelivery pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityDelivery query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityDelivery whereActivityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityDelivery whereAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityDelivery whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityDelivery whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityDelivery whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityDelivery whereInboxUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityDelivery whereLastAttemptAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityDelivery whereNextRetryAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityDelivery whereProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityDelivery whereStatus($value)
 *
 * @mixin \Eloquent
 */
class ActivityDelivery extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'activity_id',
        'inbox_url',
        'profile_id',
        'attempts',
        'status',
        'last_attempt_at',
        'next_retry_at',
        'error_message',
        'created_at',
    ];

    protected $casts = [
        'attempts' => 'integer',
        'last_attempt_at' => 'datetime',
        'next_retry_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDue($query, $asOf = null)
    {
        $asOf = $asOf ?: now();

        return $query->where('status', 'pending')
            ->whereNotNull('next_retry_at')
            ->where('next_retry_at', '<=', $asOf);
    }
}
