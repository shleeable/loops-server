<?php

namespace App\Models;

use App\Concerns\HasSnowflakePrimary;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $profile_id
 * @property int|null $video_id
 * @property int $comment_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Comment $comment
 * @property-read \App\Models\Profile $profile
 * @property-read \App\Models\Video|null $video
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentRepost newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentRepost newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentRepost query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentRepost whereCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentRepost whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentRepost whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentRepost whereProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentRepost whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentRepost whereVideoId($value)
 *
 * @mixin \Eloquent
 */
class CommentRepost extends Model
{
    use HasSnowflakePrimary;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    protected $fillable = [
        'profile_id',
        'video_id',
        'comment_id',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}
