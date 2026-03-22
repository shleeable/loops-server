<?php

namespace App\Models;

use App\Concerns\HasSnowflakePrimary;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PrivateMediaToken extends Model
{
    use HasSnowflakePrimary;

    protected $fillable = [
        'id',
        'profile_id',
        'disk',
        'tokenable_type',
        'tokenable_id',
        'path',
        'mime_type',
        'expires_at',
        'last_accessed_at',
    ];

    protected $casts = [
        'id' => 'string',
        'tokenable_id' => 'string',
        'expires_at' => 'datetime',
        'last_accessed_at' => 'datetime',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isOwnedBy(int $profileId): bool
    {
        return (int) $this->profile_id === $profileId;
    }

    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }
}
