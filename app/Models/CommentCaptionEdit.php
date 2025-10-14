<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $profile_id
 * @property string $comment_id
 * @property string|null $caption
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Comment $comment
 * @property-read \App\Models\Profile $profile
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentCaptionEdit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentCaptionEdit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentCaptionEdit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentCaptionEdit whereCaption($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentCaptionEdit whereCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentCaptionEdit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentCaptionEdit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentCaptionEdit whereProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentCaptionEdit whereUpdatedAt($value)
 */
class CommentCaptionEdit extends Model
{
    public $guarded = [];

    public $visible = [
        'comment_id',
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
            'comment_id' => 'string',
        ];
    }

    /** @return BelongsTo<Profile, $this> */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /** @return BelongsTo<Comment, $this> */
    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}
