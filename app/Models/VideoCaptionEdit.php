<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $profile_id
 * @property string $video_id
 * @property string|null $caption
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Profile $profile
 * @property-read \App\Models\Video $video
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoCaptionEdit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoCaptionEdit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoCaptionEdit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoCaptionEdit whereCaption($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoCaptionEdit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoCaptionEdit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoCaptionEdit whereProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoCaptionEdit whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VideoCaptionEdit whereVideoId($value)
 */
class VideoCaptionEdit extends Model
{
    public $guarded = [];

    public $visible = [
        'video_id',
        'profile_id',
        'caption',
        'updated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'profile_id' => 'string',
            'video_id' => 'string',
        ];
    }

    /** @return BelongsTo<Profile, $this> */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /** @return BelongsTo<Video, $this> */
    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
