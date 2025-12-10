<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoBookmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'video_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the profile that bookmarked the video.
     */
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get the bookmarked video.
     */
    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
