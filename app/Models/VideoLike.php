<?php

namespace App\Models;

use App\Observers\VideoLikeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([VideoLikeObserver::class])]
/**
 * @property int $id
 * @property int $profile_id
 * @property int $video_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Video|null $video
 * @property-read \App\Models\Profile|null $profile
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

    /** @return BelongsTo<Video, $this> */
    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
