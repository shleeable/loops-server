<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $day
 * @property int $active_30d
 * @property int $active_180d
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsageSnapshots newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsageSnapshots newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsageSnapshots query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsageSnapshots whereActive180d($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsageSnapshots whereActive30d($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsageSnapshots whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsageSnapshots whereDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsageSnapshots whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class UsageSnapshots extends Model
{
    //
}
