<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $interest_type
 * @property string|null $interest_value
 * @property int|null $interest_rank
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInterests newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInterests newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInterests query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInterests whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInterests whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInterests whereInterestRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInterests whereInterestType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInterests whereInterestValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInterests whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserInterests whereUserId($value)
 *
 * @mixin \Eloquent
 */
class UserInterests extends Model
{
    use HasFactory;
}
