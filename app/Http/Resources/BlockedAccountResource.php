<?php

namespace App\Http\Resources;

use App\Models\Profile;
use App\Models\UserFilter;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UserFilter
 */
class BlockedAccountResource extends JsonResource
{
    public function __construct(Profile|UserFilter $resource)
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
        $profile = $this->resource instanceof UserFilter
            ? AccountService::compact($this->account_id)
            : AccountService::compact($this->id);
        $blockedAt = $this->resource instanceof UserFilter
            ? $this->created_at
            : null;

        return [
            'account' => $profile,
            'blocked_at' => $blockedAt,
        ];
    }
}
