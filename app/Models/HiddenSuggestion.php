<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $profile_id
 * @property int $account_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Models\Profile $account
 * @property-read \App\Models\Profile $profile
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HiddenSuggestion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HiddenSuggestion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HiddenSuggestion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HiddenSuggestion whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HiddenSuggestion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HiddenSuggestion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HiddenSuggestion whereProfileId($value)
 *
 * @mixin \Eloquent
 */
class HiddenSuggestion extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'profile_id',
        'account_id',
    ];

    protected $casts = [
        'profile_id' => 'integer',
        'account_id' => 'integer',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    public function account()
    {
        return $this->belongsTo(Profile::class, 'account_id');
    }

    public static function hide(int $profileId, int $accountId)
    {
        return self::firstOrCreate([
            'profile_id' => $profileId,
            'account_id' => $accountId,
        ]);
    }

    public static function unhide(int $profileId, int $accountId): bool
    {
        return self::where('profile_id', $profileId)
            ->where('account_id', $accountId)
            ->delete() > 0;
    }

    public static function isHidden(int $profileId, int $accountId): bool
    {
        return self::where('profile_id', $profileId)
            ->where('account_id', $accountId)
            ->exists();
    }
}
