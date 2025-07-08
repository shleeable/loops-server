<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    private function formatBytes(int $bytes): string
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
