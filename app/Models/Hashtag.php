<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property string $name_normalized
 * @property int $can_trend
 * @property int $can_search
 * @property int $can_autolink
 * @property int $is_nsfw
 * @property int $is_banned
 * @property int $count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Video> $videos
 * @property-read int|null $videos_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hashtag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hashtag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hashtag query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hashtag whereCanAutolink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hashtag whereCanSearch($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hashtag whereCanTrend($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hashtag whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hashtag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hashtag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hashtag whereIsBanned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hashtag whereIsNsfw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hashtag whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hashtag whereNameNormalized($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Hashtag whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
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
        return $this->belongsToMany(Video::class, 'video_hashtags')
            ->using(VideoHashtag::class);
    }

    public function comments(): BelongsToMany
    {
        return $this->belongsToMany(Comment::class, 'comment_hashtags')
            ->using(CommentHashtag::class);
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
