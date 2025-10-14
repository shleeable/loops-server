<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $type
 * @property array<array-key, mixed>|null $value
 * @property string|null $action_msg
 * @property int|null $action_profile_id
 * @property string|null $activity_type
 * @property int|null $activity_id
 * @property int $visibility
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent|null $activity
 * @property-read \App\Models\Profile|null $profile
 * @property-read \App\Models\User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereActionMsg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereActionProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereActivityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereActivityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereVisibility($value)
 *
 * @mixin \Eloquent
 */
class AdminAuditLog extends Model
{
    /* Visibilty Bitmask
     *
     * 1 - Admins + Mods
     * 2 - Admins only
     */

    protected $fillable = [
        'user_id',
        'type',
        'value',
        'activity_type',
        'activity_id',
        'action_msg',
        'action_profile_id',
        'visibility',
    ];

    protected $casts = [
        'value' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'action_profile_id');
    }

    public function activity(): MorphTo
    {
        return $this->morphTo();
    }
}
