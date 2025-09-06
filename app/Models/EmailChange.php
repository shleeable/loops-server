<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailChange extends Model
{
    protected $fillable = [
        'user_id',
        'old_email',
        'new_email',
        'token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
