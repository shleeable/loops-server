<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'value',
        'activity_type',
        'activity_id',
        'ip_address',
        'client_id',
    ];

    protected $casts = [
        'value' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activity(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeFromIp($query, string $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    public function getDescriptionAttribute(): string
    {
        $descriptions = [
            'email_changed' => 'Email address was updated',
            'email_verified' => 'Email address was verified',
            'two_factor_enabled' => 'Two-factor authentication was enabled',
            'two_factor_disabled' => 'Two-factor authentication was disabled',
            'login' => 'User logged in',
            'profile_updated' => 'Profile information was updated',
            'account_deleted' => 'Account was deleted',
            'password_reset' => 'Password was reset',
            'password_changed' => 'Password was changed',
        ];

        return $descriptions[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public function isSecurityRelated(): bool
    {
        $securityTypes = [
            'password_changed',
            'email_changed',
            'email_verified',
            'two_factor_enabled',
            'two_factor_disabled',
            'login',
            'password_reset',
        ];

        return in_array($this->type, $securityTypes);
    }
}
