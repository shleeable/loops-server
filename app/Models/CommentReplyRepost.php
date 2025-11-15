<?php

namespace App\Models;

use App\Concerns\HasSnowflakePrimary;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $profile_id
 * @property int|null $video_id
 * @property int|null $comment_id
 * @property int $reply_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CommentReply $comment
 * @property-read \App\Models\Comment|null $parent
 * @property-read \App\Models\Profile $profile
 * @property-read \App\Models\Video|null $video
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyRepost newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyRepost newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyRepost query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyRepost whereCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyRepost whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyRepost whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyRepost whereProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyRepost whereReplyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyRepost whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyRepost whereVideoId($value)
 *
 * @mixin \Eloquent
 */
class CommentReplyRepost extends Model
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
        'reply_id',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class);
    }

    public function comment()
    {
        return $this->belongsTo(CommentReply::class, 'reply_id');
    }
}
