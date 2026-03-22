<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $starter_kit_id
 * @property int $hashtag_id
 * @property int $status
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Hashtag|null $hashtag
 * @property-read \App\Models\StarterKit $starterKit
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitTag approved()
 * @method static \Database\Factories\StarterKitTagFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitTag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitTag query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitTag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitTag whereHashtagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitTag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitTag whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitTag whereStarterKitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitTag whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitTag whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class StarterKitTag extends Model
{
    /**
     * Status constants
     */
    const STATUS_PENDING = 0;

    const STATUS_APPROVED = 1;

    const STATUS_REJECTED = 2;

    protected $fillable = [
        'starter_kit_id',
        'hashtag_id',
        'status',
        'order',
    ];

    protected $casts = [
        'starter_kit_id' => 'integer',
        'hashtag_id' => 'integer',
        'status' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Get the starter kit that this tag belongs to.
     */
    public function starterKit()
    {
        return $this->belongsTo(StarterKit::class);
    }

    /**
     * Get the hashtag.
     */
    public function hashtag()
    {
        return $this->belongsTo(Hashtag::class);
    }

    /**
     * Scope a query to only include approved tags.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Check if the tag is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
