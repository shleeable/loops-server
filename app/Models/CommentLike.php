<?php

namespace App\Models;

use App\Observers\CommentLikeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([CommentLikeObserver::class])]
/**
 * @property int $id
 * @property int $profile_id
 * @property int $comment_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Comment|null $comment
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentLike newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentLike newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentLike query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentLike whereCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentLike whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentLike whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentLike whereProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentLike whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class CommentLike extends Model
{
    use HasFactory;

    public $fillable = ['profile_id', 'comment_id'];

    /** @return BelongsTo<Comment, $this> */
    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}
