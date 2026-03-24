<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $profile_id
 * @property int $starter_kit_id
 * @property int $kit_status
 * @property int $order
 * @property int $kit_account_local
 * @property string|null $attestation_url
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property \Illuminate\Support\Carbon|null $rejected_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Profile $profile
 * @property-read \App\Models\StarterKit $starterKit
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitAccount approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitAccount onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitAccount pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitAccount whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitAccount whereAttestationUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitAccount whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitAccount whereKitStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitAccount whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitAccount whereProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitAccount whereRejectedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitAccount whereStarterKitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitAccount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitAccount withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitAccount withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StarterKitAccount whereKitAccountLocal($value)
 *
 * @mixin \Eloquent
 */
class StarterKitAccount extends Model
{
    use SoftDeletes;

    /**
     * Status constants
     */
    const STATUS_PENDING = 0;

    const STATUS_APPROVED = 1;

    const STATUS_REJECTED = 2;

    const STATUS_APPROVED_PENDING_ADMIN_REVIEW = 5;

    protected $fillable = [
        'profile_id',
        'starter_kit_id',
        'kit_status',
        'order',
        'kit_account_local',
        'attestation_url',
        'remote_object_id',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'profile_id' => 'integer',
        'starter_kit_id' => 'integer',
        'kit_status' => 'integer',
        'order' => 'integer',
        'kit_account_local' => 'boolean',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    protected $visible = [
        'id',
        'starter_kit_id',
        'profile_id',
        'kit_status',
        'approved_at',
        'rejected_at',
        'created_at',
    ];

    /**
     * Get the profile that this account belongs to.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get the starter kit that this account belongs to.
     */
    public function starterKit(): BelongsTo
    {
        return $this->belongsTo(StarterKit::class);
    }

    /**
     * Scope a query to only include approved accounts.
     */
    public function scopeApproved($query)
    {
        return $query->where('kit_status', self::STATUS_APPROVED);
    }

    /**
     * Scope a query to only include pending accounts.
     */
    public function scopePending($query)
    {
        return $query->where('kit_status', self::STATUS_PENDING);
    }

    public function getPermalink()
    {
        if ($this->kit_account_local) {
            return url('/ap/kit/'.$this->starter_kit_id.'/items/'.$this->profile_id);
        }

    }

    public function getAccountPermalink()
    {
        if ($this->kit_account_local) {
            return url('/ap/users/'.$this->profile_id);
        }

    }

    public function getAttestationUrl()
    {
        if ($this->kit_account_local) {
            return url('/ap/kit/'.$this->starter_kit_id.'/attestation/'.$this->id);
        }

        return $this->attestation_url;
    }

    /**
     * Check if the account is approved.
     */
    public function isApproved(): bool
    {
        return $this->kit_status === self::STATUS_APPROVED;
    }

    public function getStatusString(): string
    {
        return match ($this->kit_status) {
            0 => 'pending',
            1 => 'approved',
            2 => 'rejected',
            default => 'pending'
        };
    }

    /**
     * Approve the account.
     */
    public function approve()
    {
        $this->update([
            'kit_status' => self::STATUS_APPROVED,
            'approved_at' => now(),
            'rejected_at' => null,
        ]);
    }

    /**
     * Reject the account.
     */
    public function reject()
    {
        $this->update([
            'kit_status' => self::STATUS_REJECTED,
            'rejected_at' => now(),
            'approved_at' => null,
        ]);
    }

    public function toPublicArray(): array
    {
        return [
            'id' => (string) $this->id,
            'starter_kit_id' => (string) $this->starter_kit_id,
            'profile_id' => (string) $this->profile_id,
            'kit_status' => $this->kit_status,
            'approved_at' => $this->approved_at,
            'rejected_at' => $this->rejected_at,
            'created_at' => $this->created_at,
        ];
    }
}
