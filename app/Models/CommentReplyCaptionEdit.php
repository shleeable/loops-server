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
 * @property-read \App\Models\CommentReply $comment
 * @property-read \App\Models\Profile $profile
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyCaptionEdit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyCaptionEdit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyCaptionEdit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyCaptionEdit whereCaption($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyCaptionEdit whereCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyCaptionEdit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyCaptionEdit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyCaptionEdit whereProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommentReplyCaptionEdit whereUpdatedAt($value)
 */
class CommentReplyCaptionEdit extends Model
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

    /** @return BelongsTo<CommentReply, $this> */
    public function comment()
    {
        return $this->belongsTo(CommentReply::class);
    }
}
