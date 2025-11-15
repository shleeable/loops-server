<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $mentionable_type
 * @property int $mentionable_id
 * @property string $username
 * @property int $start_index
 * @property int $end_index
 * @property bool $is_local
 * @property int|null $profile_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $mentionable
 * @property-read \App\Models\Profile|null $mentionedUser
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mention newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mention newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mention query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mention whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mention whereEndIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mention whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mention whereIsLocal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mention whereMentionableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mention whereMentionableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mention whereProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mention whereStartIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mention whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mention whereUsername($value)
 *
 * @mixin \Eloquent
 */
class Mention extends Model
{
    protected $fillable = [
        'mentionable_type',
        'mentionable_id',
        'username',
        'start_index',
        'end_index',
        'profile_id',
        'is_local',
    ];

    public $visible = [
        'username',
        'start_index',
        'end_index',
        'profile_id',
        'is_local',
    ];

    public $casts = [
        'profile_id' => 'string',
        'is_local' => 'boolean',
    ];

    public function mentionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function mentionedUser(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }
}
