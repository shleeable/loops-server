<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $old_email
 * @property string $new_email
 * @property string $token
 * @property \Illuminate\Support\Carbon $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailChange newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailChange newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailChange query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailChange whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailChange whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailChange whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailChange whereNewEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailChange whereOldEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailChange whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailChange whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailChange whereUserId($value)
 *
 * @mixin \Eloquent
 */
class EmailChange extends Model
{
    protected $fillable = [
        'user_id',
        'old_email',
        'new_email',
        'token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
