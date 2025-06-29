<?php

namespace App\Models;

use App\Observers\VideoLikeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([VideoLikeObserver::class])]
class VideoLike extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'profile_id',
        'video_id',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
