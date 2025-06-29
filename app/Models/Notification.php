<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    /**
     * Types Bitmask
     * 0-10 = Reserved
     * 11 = New Follower
     * 12 = Reserved
     * 13 = Someone you know joined Loops
     * 14 = Your account is getting noticed
     * 15 = New Video Comment
     * 16 = New Video Comment Reply
     * 17 = Reserved
     * 18 = Reserved
     * 19 = Reserved
     * 20 = Reserved
     * 21 = Video Like
     * 22 = Comment Like
     * 23 = Comment Reply Like
     **/
    public const NEW_FOLLOWER = 11;

    public const NEW_VIDCOMMENT = 15;

    public const NEW_VIDCOMMENTREPLY = 16;

    public const VIDEO_LIKE = 21;

    public const VIDEO_COMMENT_LIKE = 22;

    public const VIDEO_COMMENT_REPLY_LIKE = 23;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'type', 'video_id', 'profile_id', 'comment_id', 'comment_reply_id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'meta' => 'json',
        ];
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
