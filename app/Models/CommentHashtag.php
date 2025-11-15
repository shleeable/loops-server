<?php

namespace App\Models;

use App\Observers\CommentHashtagObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[ObservedBy([CommentHashtagObserver::class])]
/**
 * @property int $id
 * @property int $comment_id
 * @property int $hashtag_id
 * @property int $visibility
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentHashtag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentHashtag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentHashtag query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentHashtag whereCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentHashtag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentHashtag whereHashtagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentHashtag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentHashtag whereUpdatedAt($value)
 */
class CommentHashtag extends Pivot
{
    protected $table = 'comment_hashtags';

    public $incrementing = true;

    protected $fillable = ['comment_id', 'hashtag_id', 'visibility'];

    public $timestamps = false;
}
