<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $status
 * @property string|null $file_path
 * @property string|null $file_size
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property array<array-key, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string|null $download_url
 * @property-read string $formatted_size
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataExport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataExport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataExport query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataExport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataExport whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataExport whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataExport whereFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataExport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataExport whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataExport whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataExport whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataExport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataExport whereUserId($value)
 *
 * @mixin \Eloquent
 */
class DataExport extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'status',
        'file_path',
        'file_size',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    public function getFormattedSizeAttribute(): string
    {
        if (! $this->file_size) {
            return 'Calculating...';
        }

        return $this->formatBytes($this->file_size);
    }

    public function getDownloadUrlAttribute(): ?string
    {
        if ($this->status !== 'ready' || $this->isExpired()) {
            return null;
        }

        return route('export.download', $this->id);
    }

    private function formatBytes(int|string $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < 4; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($export) {
            if ($export->status === 'ready') {
                $export->expires_at = Carbon::now()->addDays(7);
            }
        });
    }
}
