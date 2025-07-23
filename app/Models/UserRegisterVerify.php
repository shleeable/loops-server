<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRegisterVerify extends Model
{
    protected $fillable = [
        'session_key',
        'email',
        'verify_code',
        'resend_attempts',
        'email_last_sent_at',
        'verified_at',
    ];

    protected $casts = [
        'resend_attempts' => 'integer',
        'email_last_sent_at' => 'datetime',
        'verified_at' => 'datetime',
    ];
}
