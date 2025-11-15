<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $profile_id
 * @property int $following_id
 * @property bool $profile_is_local
 * @property bool $following_is_local
 * @property int $following_state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Profile $actor
 * @property-read \App\Models\Profile $target
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest whereFollowingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest whereFollowingIsLocal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest whereFollowingState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest whereProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest whereProfileIsLocal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class FollowRequest extends Model
{
    protected $fillable = [
        'profile_id',
        'following_id',
        'profile_is_local',
        'following_is_local',
        'following_state',
    ];

    protected $casts = [
        'profile_is_local' => 'boolean',
        'following_is_local' => 'boolean',
    ];

    public function getState()
    {
        return match ($this->following_state) {
            0 => 'pending',
            1 => 'delivery_error',
            2 => 'unavailable',
            3 => 'rejected',
            4 => 'approved',
            5 => 'pending_delete',
            default => 'unknown',
        };
    }

    public function actor()
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    public function target()
    {
        return $this->belongsTo(Profile::class, 'following_id');
    }
}
