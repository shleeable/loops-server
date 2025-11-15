<?php

namespace App\Models;

use App\Concerns\HasSnowflakePrimary;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $profile_id
 * @property int $video_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Profile $profile
 * @property-read \App\Models\Video $video
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoRepost newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoRepost newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoRepost query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoRepost whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoRepost whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoRepost whereProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoRepost whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoRepost whereVideoId($value)
 *
 * @mixin \Eloquent
 */
class VideoRepost extends Model
{
    use HasSnowflakePrimary;

    public $incrementing = false;

    protected $fillable = [
        'profile_id',
        'video_id',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
