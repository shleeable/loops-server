<?php

namespace App\Models;

use App\Concerns\HasSnowflakePrimary;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;

/**
 * @property int $id
 * @property string $email
 * @property string|null $username_requested
 * @property string|null $birthdate_encrypted
 * @property int|null $age_at_submission
 * @property string $reason
 * @property string|null $fediverse_account
 * @property array<array-key, mixed>|null $custom_answers
 * @property string $status
 * @property string|null $status_reason
 * @property int|null $reviewed_by
 * @property \Illuminate\Support\Carbon|null $reviewed_at
 * @property string|null $ip_hash
 * @property int|null $user_id
 * @property string|null $email_verification_token
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $birthdate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CuratedApplicationNote> $notes
 * @property-read int|null $notes_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\CuratedApplicationOnboarding|null $onboarding
 * @property-read \App\Models\User|null $reviewer
 * @property-read \App\Models\User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication autoRejected()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication emailVerified()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication expired()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication ready()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication rejected()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication stale(int $days = 30)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication whereAgeAtSubmission($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication whereBirthdateEncrypted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication whereCustomAnswers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication whereEmailVerificationToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication whereFediverseAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication whereIpHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication whereMagicLinkKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication whereReviewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication whereReviewedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication whereStatusReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication whereTemporaryPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CuratedApplication whereUsernameRequested($value)
 *
 * @mixin \Eloquent
 */
class CuratedApplication extends Model
{
    use HasFactory, HasSnowflakePrimary, Notifiable;

    public $incrementing = false;

    protected $keyType = 'string';

    public function routeNotificationForMail(): string
    {
        return $this->email;
    }

    protected $fillable = [
        'email',
        'username_requested',
        'birthdate_encrypted',
        'age_at_submission',
        'reason',
        'fediverse_account',
        'custom_answers',
        'status',
        'status_reason',
        'reviewed_by',
        'reviewed_at',
        'ip_hash',
        'user_id',
        'email_verification_token',
        'email_verified_at',
    ];

    protected $casts = [
        'custom_answers' => 'array',
        'reviewed_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'age_at_submission' => 'integer',
    ];

    protected $hidden = [
        'birthdate_encrypted',
        'ip_hash',
        'email_verification_token',
    ];

    const STATUS_PENDING = 'pending';

    const STATUS_APPROVED = 'approved';

    const STATUS_READY = 'ready';

    const STATUS_REJECTED = 'rejected';

    const STATUS_EXPIRED = 'expired';

    const STATUS_AUTO_REJECTED = 'auto_rejected';

    public function setBirthdateAttribute(Carbon $date): void
    {
        $this->attributes['birthdate_encrypted'] = Crypt::encryptString($date->toDateString());
        $this->attributes['age_at_submission'] = $date->age;
    }

    public function getBirthdateAttribute(): ?Carbon
    {
        if (empty($this->attributes['birthdate_encrypted'])) {
            return null;
        }

        return Carbon::parse(
            Crypt::decryptString($this->attributes['birthdate_encrypted'])
        );
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeReady($query)
    {
        return $query->where('status', self::STATUS_READY);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeAutoRejected($query)
    {
        return $query->where('status', self::STATUS_AUTO_REJECTED);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }

    public function scopeEmailVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeStale($query, int $days = 30)
    {
        return $query->pending()
            ->where('created_at', '<', now()->subDays($days));
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function onboarding(): HasOne
    {
        return $this->hasOne(CuratedApplicationOnboarding::class, 'application_id', 'id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(CuratedApplicationNote::class, 'application_id')
            ->orderBy('created_at', 'desc');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isReady(): bool
    {
        return $this->status === self::STATUS_READY;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return in_array($this->status, [self::STATUS_REJECTED, self::STATUS_AUTO_REJECTED]);
    }

    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function markEmailVerified(): void
    {
        $this->update([
            'status' => 'ready',
            'email_verified_at' => now(),
            'email_verification_token' => null,
        ]);
    }
}
