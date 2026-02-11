<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminInvite extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invite_key',
        'title',
        'message',
        'admin_note',
        'autofollow_accounts',
        'verify_email',
        'invited_by',
        'email_admin_on_join',
        'max_uses',
        'total_uses',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'autofollow_accounts' => 'array',
        'verify_email' => 'boolean',
        'is_active' => 'boolean',
        'email_admin_on_join' => 'boolean',
        'expires_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected function scopeUnexpired(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    protected function scopeExpired(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNotNull('expires_at')
                ->where('expires_at', '<', now());
        });
    }

    public function getInviteLink()
    {
        return url('/invite/'.$this->invite_key);
    }

    public function getAdminLink()
    {
        return url('/admin/invites/'.$this->id);
    }
}
