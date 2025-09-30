<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $profile_id
 * @property int $comment_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CommentReply $comment
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyLike newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyLike newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyLike query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyLike whereCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyLike whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyLike whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyLike whereProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyLike whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class CommentReplyLike extends Model
{
    use HasFactory;

    public $fillable = ['profile_id', 'comment_id'];

    public function comment()
    {
        return $this->belongsTo(CommentReply::class);
    }
}
