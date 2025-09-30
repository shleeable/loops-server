<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $session_key
 * @property string $email
 * @property string $verify_code
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property int $resend_attempts
 * @property \Illuminate\Support\Carbon|null $email_last_sent_at
 * @property \Illuminate\Support\Carbon|null $verified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserRegisterVerify newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserRegisterVerify newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserRegisterVerify query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserRegisterVerify whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserRegisterVerify whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserRegisterVerify whereEmailLastSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserRegisterVerify whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserRegisterVerify whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserRegisterVerify whereResendAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserRegisterVerify whereSessionKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserRegisterVerify whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserRegisterVerify whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserRegisterVerify whereVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserRegisterVerify whereVerifyCode($value)
 *
 * @mixin \Eloquent
 */
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
