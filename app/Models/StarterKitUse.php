<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $profile_id
 * @property int $starter_kit_id
 * @property array<array-key, mixed>|null $followed_profile_ids
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Profile $profile
 * @property-read \App\Models\StarterKit $starterKit
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitUse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitUse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitUse query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitUse whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitUse whereFollowedProfileIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitUse whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitUse whereProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitUse whereStarterKitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitUse whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class StarterKitUse extends Model
{
    protected $fillable = [
        'profile_id',
        'starter_kit_id',
        'followed_profile_ids',
    ];

    protected $casts = [
        'followed_profile_ids' => 'array',
    ];

    /**
     * Get the profile that used this starter kit.
     */
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get the starter kit that was used.
     */
    public function starterKit()
    {
        return $this->belongsTo(StarterKit::class);
    }

    /**
     * Check if a profile has already used a starter kit.
     */
    public static function hasUsed(int $profileId, int $starterKitId): bool
    {
        return static::whereProfileId($profileId)
            ->whereStarterKitId($starterKitId)
            ->exists();
    }

    /**
     * Record a profile using a starter kit, ignoring duplicates.
     */
    public static function record(int $profileId, int $starterKitId): self
    {
        return static::firstOrCreate([
            'profile_id' => $profileId,
            'starter_kit_id' => $starterKitId,
        ]);
    }
}
