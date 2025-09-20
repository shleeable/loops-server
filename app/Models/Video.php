<?php

namespace App\Models;

use App\Concerns\HasSnowflakePrimary;
use App\Observers\VideoObserver;
use App\Services\HashidService;
use DB;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Storage;

#[ObservedBy([VideoObserver::class])]
class Video extends Model
{
    use HasFactory, HasSnowflakePrimary;

    /**
     * Status Bitmask
     * 0 = Unused
     * 1 = Pending Transcoding
     * 2 = Published
     * 3 = Reserved
     * 4 = Archived
     * 5 = Reserved
     * 6 = Admin unpublished
     **/

    /**
     * Visibility Bitmask
     * 0 = Unused
     * 1 = Public
     * 2 = Unused
     * 3 = Unused
     * 4 = Unlisted (not in feeds, direct link only)
     * 5 = Followers only
     **/

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    public $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @property array<string, mixed>|null $media_metadata
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'integer',
            'profile_id' => 'string',
            'is_sensitive' => 'boolean',
            'media_metadata' => 'array',
            'is_local' => 'boolean',
        ];
    }

    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('status', 2);
    }

    #[Scope]
    protected function canComment(Builder $query): void
    {
        $query->where('comment_state', 4);
    }

    public function statusLabel()
    {
        return match ($this->status) {
            1 => 'pending',
            2 => 'published',
            4 => 'archived',
            6 => 'unpublished',
            default => null
        };
    }

    public function hashid()
    {
        return HashidService::encode($this->id);
    }

    public function thumb()
    {
        $thumb = url('/storage/videos/video-placeholder.jpg');
        if ($this->has_thumb) {
            $ext = pathinfo($this->vid, PATHINFO_EXTENSION);
            $url = str_replace('.'.$ext, '.jpg', $this->vid);
            $thumb = Storage::disk('s3')->url($url);
        }

        return $thumb;
    }

    public function getObjectUrl()
    {
        return url('/ap/users/'.$this->profile_id.'/video/'.$this->id);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function shareUrl(): string
    {
        return url('/v/'.HashidService::encode($this->id));
    }

    public function mediaUrl(): string
    {
        return Storage::disk('s3')
            ->url(
                $this->has_processed && $this->vid_optimized ?
                    $this->vid_optimized :
                    $this->vid
            );
    }

    public function recalculateCommentsCount(): int
    {
        $actualCount = DB::selectOne("
            SELECT (
                (SELECT COUNT(*) FROM comments
                 WHERE video_id = ?)
                +
                (SELECT COUNT(*) FROM comment_replies
                 WHERE video_id = ? AND status = 'active' AND deleted_at IS NULL)
            ) as total_count
        ", [$this->id, $this->id])->total_count;

        $this->update(['comments' => $actualCount]);

        return $actualCount;
    }

    public function hashtags(): BelongsToMany
    {
        return $this->belongsToMany(Hashtag::class, 'video_hashtags');
    }

    public function syncHashtagsFromCaption()
    {
        preg_match_all('/#([A-Za-z0-9_-]{1,30})/', $this->caption, $matches);
        $hashtags = [];

        foreach ($matches[1] as $tag) {
            $hashtag = Hashtag::firstOrCreate([
                'name' => $tag,
            ]);
            if ($hashtag->can_autolink) {
                $hashtags[] = $hashtag->id;
            }
        }

        $this->hashtags()->sync($hashtags);
    }
}
