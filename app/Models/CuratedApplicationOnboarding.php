<?php

namespace App\Models;

use App\Concerns\HasSnowflakePrimary;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $application_id
 * @property string|null $email
 * @property string|null $username_requested
 * @property string|null $magic_key
 * @property \Illuminate\Support\Carbon|null $expires_after
 * @property \Illuminate\Support\Carbon|null $joined_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CuratedApplication|null $application
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationOnboarding newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationOnboarding newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationOnboarding query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationOnboarding whereApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationOnboarding whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationOnboarding whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationOnboarding whereExpiresAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationOnboarding whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationOnboarding whereJoinedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationOnboarding whereMagicKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationOnboarding whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplicationOnboarding whereUsernameRequested($value)
 *
 * @mixin \Eloquent
 */
class CuratedApplicationOnboarding extends Model
{
    use HasSnowflakePrimary;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'application_id',
        'email',
        'username_requested',
        'magic_key',
        'joined_at',
        'expires_after',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'expires_after' => 'datetime',
    ];

    public function onboardingUrl()
    {
        $params = http_build_query(['email' => $this->email, 'key' => $this->magic_key]);

        return url('/auth/curated/complete?'.$params);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(CuratedApplication::class, 'application_id');
    }

    public function scopePending($query)
    {
        return $query->whereNull('joined_at');
    }

    public function scopeStale($query, int $days = 30)
    {
        return $query->pending()->where('expires_after', '<', now());
    }
}
