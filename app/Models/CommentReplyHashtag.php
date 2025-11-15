<?php

namespace App\Models;

use App\Observers\CommentReplyHashtagObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[ObservedBy([CommentReplyHashtagObserver::class])]
/**
 * @property int $id
 * @property int $reply_id
 * @property int $hashtag_id
 * @property int $visibility
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyHashtag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyHashtag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyHashtag query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyHashtag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyHashtag whereHashtagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyHashtag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyHashtag whereReplyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyHashtag whereUpdatedAt($value)
 */
class CommentReplyHashtag extends Pivot
{
    protected $table = 'comment_reply_hashtags';

    public $incrementing = true;

    protected $fillable = ['reply_id', 'hashtag_id', 'visibility'];

    public $timestamps = false;
}
