<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Hashtag extends Model
{
    protected $fillable = [
        'name',
        'name_normalized',
        'count',
        'can_trend',
        'can_search',
        'can_autolink',
        'is_nsfw',
        'is_banned',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($hashtag) {
            $hashtag->name_normalized = strtolower($hashtag->name);
        });
    }

    public static function findByName(string $name): ?self
    {
        return static::where('name_normalized', strtolower($name))->whereCanSearch(true)->first();
    }

    public function videos(): BelongsToMany
    {
        return $this->belongsToMany(Video::class, 'video_hashtags');
    }

    public function updateCount(): void
    {
        $this->count = $this->videos()->count();
        $this->save();
    }

    public function setIsBannedAttribute($value)
    {
        $this->attributes['is_banned'] = $value;

        if ($value === true) {
            $this->attributes['can_autolink'] = false;
            $this->attributes['can_search'] = false;
            $this->attributes['can_trend'] = false;
        }
    }
}
