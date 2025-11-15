<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProfileAvatar extends Model
{
    protected $fillable = [
        'profile_id',
        'remote_url',
        'path',
        'mime',
        'size',
        'skip_refetch',
        'is_invalid',
        'last_fetched_at',
    ];

    protected $casts = [
        'skip_refetch' => 'boolean',
        'is_invalid' => 'boolean',
        'last_fetched_at' => 'datetime',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function getUrlAttribute(): ?string
    {
        return $this->path ? Storage::disk('s3')->url($this->path) : null;
    }

    public function shouldRefetch(): bool
    {
        if ($this->skip_refetch || $this->is_invalid) {
            return false;
        }

        // Refetch if never fetched or older than 30 days
        return ! $this->last_fetched_at ||
               $this->last_fetched_at->lt(now()->subDays(30));
    }

    public function delete(): ?bool
    {
        // Delete from S3 when model is deleted
        if ($this->path && Storage::disk('s3')->exists($this->path)) {
            Storage::disk('s3')->delete($this->path);
        }

        return parent::delete();
    }
}
