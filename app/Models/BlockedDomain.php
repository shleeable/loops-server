<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedDomain extends Model
{
    protected $fillable = [
        'domain',
        'is_subdomain',
        'reason',
        'blocked_by',
    ];

    protected $casts = [
        'is_subdomain' => 'boolean',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->domain = strtolower($model->domain);
        });
    }
}
