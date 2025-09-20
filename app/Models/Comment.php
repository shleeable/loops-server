<?php

namespace App\Models;

use App\Concerns\HasSnowflakePrimary;
use App\Services\HashidService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, HasSnowflakePrimary, SoftDeletes;

    protected $fillable = ['video_id', 'profile_id', 'caption', 'status'];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'entities' => 'array',
            'likes' => 'integer',
            'replies' => 'integer',
            'can_reply' => 'boolean',
            'is_pinned' => 'boolean',
            'is_edited' => 'boolean',
            'is_hidden' => 'boolean',
            'is_sensitive' => 'boolean',
        ];
    }

    public function children(): HasMany
    {
        return $this->hasMany(CommentReply::class, 'comment_id', 'id');
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function getObjectUrl()
    {
        return url('/ap/users/'.$this->profile_id.'/comment/'.$this->id);
    }

    public function shareUrl(): string
    {
        $vid = HashidService::encode($this->video_id);
        $cid = HashidService::encode($this->id);

        return "/v/{$vid}?cid={$cid}";
    }

    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
