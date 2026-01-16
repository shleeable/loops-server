<?php

namespace App\Http\Resources;

use App\Models\AdminInvite;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AdminInvite
 */
class AdminInviteResource extends JsonResource
{
    public function __construct(AdminInvite $resource)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $expired = false;
        if ($this->expires_at) {
            $expired = now()->gt($this->expires_at);
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'message' => $this->message,
            'admin_note' => $this->admin_note,
            'is_active' => $this->is_active && ! $expired,
            'verify_email' => $this->verify_email,
            'email_admin_on_join' => $this->email_admin_on_join,
            'max_uses' => $this->max_uses,
            'autofollow_accounts' => $this->autofollow_accounts,
            'invite_url' => $this->getInviteLink(),
            'total_uses' => $this->total_uses,
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
