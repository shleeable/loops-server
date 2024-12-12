<?php

namespace App\Models;

use App\Concerns\HasSnowflakePrimary;
use App\Services\HashidService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Storage;

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

    public function hashid()
    {
        return HashidService::encode($this->id);
    }

    public function thumb()
    {
        $thumb = 'https://loopsusercontent.com/videos/video-placeholder.jpg';
        if ($this->has_thumb) {
            $ext = pathinfo($this->vid, PATHINFO_EXTENSION);
            $url = str_replace('.' . $ext, '.jpg', $this->vid);
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
        return url('/v/' . HashidService::encode($this->id));
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
