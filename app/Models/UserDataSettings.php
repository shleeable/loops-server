<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDataSettings extends Model
{
    protected $fillable = [
        'user_id',
        'data_retention_period',
        'analytics_tracking',
        'research_data_sharing',
    ];

    protected $casts = [
        'analytics_tracking' => 'boolean',
        'research_data_sharing' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getRetentionInMonths(): ?int
    {
        return match ($this->data_retention_period) {
            '1year' => 12,
            '2years' => 24,
            '5years' => 60,
            'never' => null,
            default => null
        };
    }
}
