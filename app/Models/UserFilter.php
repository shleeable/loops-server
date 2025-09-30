<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $profile_id
 * @property int $account_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Profile $account
 * @property-read \App\Models\Profile $profile
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserFilter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserFilter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserFilter query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserFilter whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserFilter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserFilter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserFilter whereProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserFilter whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class UserFilter extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function account()
    {
        return $this->belongsTo(Profile::class, 'account_id');
    }
}
