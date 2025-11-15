<?php

namespace App\Http\Resources;

use App\Models\Profile;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Profile
 */
class AccountCompactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $account = AccountService::compact($this->id);

        if (isset($this->is_following)) {
            $account['is_following'] = (bool) $this->is_following;
        }

        return $account;
    }
}
