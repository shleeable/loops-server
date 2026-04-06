<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuratedApplicationNote extends Model
{
    protected $fillable = [
        'application_id',
        'admin_id',
        'body',
    ];

    protected $casts = [
        'id' => 'string',
        'application_id' => 'string',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(CuratedApplication::class, 'application_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
