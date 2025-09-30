<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $content
 * @property string $status
 * @property string $location
 * @property bool $system_page
 * @property array<array-key, mixed>|null $meta
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page draft()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereSystemPage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'status',
        'meta',
        'location',
        'created_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'system_page' => 'boolean',
    ];

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }
}
