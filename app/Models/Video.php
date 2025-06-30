<?php

namespace App\Models;

use App\Concerns\HasSnowflakePrimary;
use App\Observers\VideoObserver;
use App\Services\HashidService;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    public $guarded = [];

    /**
     * Get the attributes that should be cast.
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
        $thumb = 'https://loopsusercontent.com/videos/video-placeholder.jpg';
        if ($this->has_thumb) {
            $ext = pathinfo($this->vid, PATHINFO_EXTENSION);
            $url = str_replace('.'.$ext, '.jpg', $this->vid);
            $thumb = Storage::disk('s3')->url($url);
        }

        return $thumb;
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
}
