<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $key_id
 * @property string|null $title
 * @property string|null $slug
 * @property string $body
 * @property int $type
 * @property string|null $link
 * @property array<array-key, mixed>|null $metadata
 * @property bool $is_active
 * @property bool $has_public_link
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $notifications_generated_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read int $total_notification_count
 * @property-read int $unread_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notification> $notifications
 * @property-read int|null $notifications_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemMessage active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemMessage needsNotificationGeneration()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemMessage published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemMessage whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemMessage whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemMessage whereHasPublicLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemMessage whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemMessage whereKeyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemMessage whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemMessage whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemMessage whereNotificationsGeneratedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemMessage wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemMessage whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemMessage whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemMessage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemMessage whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class SystemMessage extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key_id',
        'title',
        'slug',
        'body',
        'type',
        'link',
        'metadata',
        'is_active',
        'published_at',
        'has_public_link',
        'expires_at',
        'notifications_generated_at',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'has_public_link' => 'boolean',
        'is_active' => 'boolean',
        'published_at' => 'datetime',
        'notifications_generated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'json',
    ];

    /**
     * Get all notifications for this system message.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'system_message_id');
    }

    /**
     * Scope to get only active messages.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only published messages.
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope to get messages that need notifications generated.
     */
    public function scopeNeedsNotificationGeneration($query)
    {
        return $query->whereNull('notifications_generated_at')
            ->published()
            ->active();
    }

    /**
     * Get the unread count for this system message.
     */
    public function getUnreadCountAttribute(): int
    {
        return $this->notifications()
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Get the total notification count for this system message.
     */
    public function getTotalNotificationCountAttribute(): int
    {
        return $this->notifications()->count();
    }
}
