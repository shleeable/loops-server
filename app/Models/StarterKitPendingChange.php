<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $starter_kit_id
 * @property int $profile_id
 * @property array $original
 * @property array $changeset
 * @property string $status
 * @property bool $bundled_with_kit_review
 * @property int|null $reviewed_by
 * @property \Illuminate\Support\Carbon|null $applied_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Profile|null $profile
 * @property-read \App\Models\Profile|null $reviewer
 * @property-read \App\Models\StarterKit $starterKit
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitPendingChange newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitPendingChange newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitPendingChange pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitPendingChange query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitPendingChange whereAppliedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitPendingChange whereBundledWithKitReview($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitPendingChange whereChangeset($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitPendingChange whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitPendingChange whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitPendingChange whereOriginal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitPendingChange whereProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitPendingChange whereReviewedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitPendingChange whereStarterKitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitPendingChange whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitPendingChange whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class StarterKitPendingChange extends Model
{
    protected $fillable = [
        'starter_kit_id',
        'profile_id',
        'original',
        'changeset',
        'status',
        'bundled_with_kit_review',
        'reviewed_by',
        'applied_at',
    ];

    protected $casts = [
        'original' => 'array',
        'changeset' => 'array',
        'bundled_with_kit_review' => 'boolean',
        'applied_at' => 'datetime',
    ];

    const REVIEWABLE_FIELDS = ['title', 'description', 'hashtags', 'icon_path', 'header_path'];

    const MEDIA_FIELDS = ['icon_path', 'header_path'];

    public function starterKit(): BelongsTo
    {
        return $this->belongsTo(StarterKit::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'reviewed_by');
    }

    public function getChangesetAttribute($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (empty($value)) {
            return [];
        }

        return json_decode($value, true) ?? [];
    }

    public function getOriginalAttribute($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (empty($value)) {
            return [];
        }

        return json_decode($value, true) ?? [];
    }

    public function getFieldStatus(string $field): string
    {
        return $this->changeset[$field]['status'] ?? 'pending';
    }

    public function allFieldsReviewed()
    {
        $changes = $this->changeset ?? [];

        foreach ($changes as $fieldName => $field) {
            if (($field['status'] ?? null) === 'pending') {
                return false;
            }
        }

        return true;
    }

    public function hasAnyApprovedFields(): bool
    {
        foreach ($this->changeset as $field) {
            if (($field['status'] ?? null) === 'approved') {
                return true;
            }
        }

        return false;
    }

    public function hasAnyRejectedFields(): bool
    {
        foreach ($this->changeset as $field) {
            if (($field['status'] ?? null) === 'rejected') {
                return true;
            }
        }

        return false;
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'in_review']);
    }
}
