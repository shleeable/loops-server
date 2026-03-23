<?php

namespace App\Http\Resources;

use App\Models\Profile;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Profile
 */
class StarterKitAccountSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $account = AccountService::get($this->id);

        if (isset($this->is_following)) {
            $account['is_following'] = (bool) $this->is_following;
        }

        if ($pid = $request->user()?->profile_id) {
            $account['is_owner'] = (int) $this->id === (int) $pid;
        }

        $account['starter_kit_state'] = $this->starter_kit_state ?? 5;

        return $account;
    }
}
