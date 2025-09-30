<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $video_cursor
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserCursor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserCursor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserCursor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserCursor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserCursor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserCursor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserCursor whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserCursor whereVideoCursor($value)
 *
 * @mixin \Eloquent
 */
class UserCursor extends Model
{
    use HasFactory;
}
