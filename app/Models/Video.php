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
