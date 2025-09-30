<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $profile_id
 * @property int $video_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Video|null $video
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoLike newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoLike newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoLike query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoLike whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoLike whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoLike whereProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoLike whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoLike whereVideoId($value)
 *
 * @mixin \Eloquent
 */
class VideoLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'video_id',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
