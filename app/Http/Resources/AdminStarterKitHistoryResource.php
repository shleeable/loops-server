<?php

namespace App\Http\Resources;

use App\Models\AdminAuditLog;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AdminAuditLog
 */
class AdminStarterKitHistoryResource extends JsonResource
{
    public function __construct(AdminAuditLog $resource)
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
        $actor = AccountService::compact($this->user_id) ?? [
            'id' => '0',
            'username' => 'system',
            'name' => 'system',
            'avatar' => url('/storage/avatars/default.jpg'),
        ];

        return [
            'id' => $this->id,
            'type' => $this->type,
            'actor' => $actor,
            'value' => $this->value,
            'created_at' => $this->created_at->format('c'),
        ];
    }
}
