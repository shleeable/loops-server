<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property array<array-key, mixed>|null $passwords
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLegacyPassword newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLegacyPassword newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLegacyPassword query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLegacyPassword whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLegacyPassword whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLegacyPassword wherePasswords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLegacyPassword whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLegacyPassword whereUserId($value)
 *
 * @mixin \Eloquent
 */
class UserLegacyPassword extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'user_id' => 'integer',
        'passwords' => 'array',
    ];
}
