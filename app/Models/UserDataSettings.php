<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $data_retention_period
 * @property bool $analytics_tracking
 * @property bool $research_data_sharing
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDataSettings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDataSettings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDataSettings query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDataSettings whereAnalyticsTracking($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDataSettings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDataSettings whereDataRetentionPeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDataSettings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDataSettings whereResearchDataSharing($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDataSettings whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserDataSettings whereUserId($value)
 *
 * @mixin \Eloquent
 */
class UserDataSettings extends Model
{
    protected $fillable = [
        'user_id',
        'data_retention_period',
        'analytics_tracking',
        'research_data_sharing',
    ];

    protected $casts = [
        'analytics_tracking' => 'boolean',
        'research_data_sharing' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getRetentionInMonths(): ?int
    {
        return match ($this->data_retention_period) {
            '1year' => 12,
            '2years' => 24,
            '5years' => 60,
            'never' => null,
            default => null
        };
    }
}
