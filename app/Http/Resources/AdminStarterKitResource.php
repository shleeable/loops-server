<?php

namespace App\Http\Resources;

use App\Models\StarterKit;
use App\Models\StarterKitPendingChange;
use App\Services\AccountService;
use App\Services\StarterKitService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StarterKit
 */
class AdminStarterKitResource extends JsonResource
{
    public function __construct(StarterKit $resource)
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
        $account = AccountService::compact($this->profile_id) ?? [];

        $user = $request->user();

        $url = $this->publicUrl();

        $accounts = app(StarterKitService::class)->allAccounts($this->id) ?? [];

        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'url' => $url,
            'path' => parse_url($url, PHP_URL_PATH),
            'remote_url' => $this->remote_url,
            'is_local' => $this->is_local,
            'is_discoverable' => $this->is_discoverable,
            'is_sensitive' => $this->is_sensitive,
            'is_owner' => (bool) $user && $user->profile_id == $this->profile_id,
            'creator' => $account,
            'uses' => $this->uses,
            'total_accounts' => $this->total_accounts,
            'approved_accounts' => $this->approved_accounts,
            'accounts' => $accounts,
            'status' => $this->status,
            'status_text' => $this->getStatusText(),
            'header_url' => $this->header_url,
            'icon_url' => $this->icon_url,
            'pending_media' => $this->resolvePendingMedia(),
            'hashtags' => $this->hashtags->map(fn ($tag) => $tag->name),
            'created_at' => $this->created_at->format('c'),
            'updated_at' => $this->updated_at->format('c'),
        ];
    }

    private function resolvePendingMedia(): array
    {
        if (! in_array($this->status, [0, 1, 2])) {
            return [];
        }

        $result = [];

        $changes = StarterKitPendingChange::where('starter_kit_id', $this->id)
            ->where('bundled_with_kit_review', true)
            ->where('status', 'pending')
            ->get();

        foreach ($changes as $change) {
            foreach ($change->changeset as $field => $data) {
                if (in_array($field, ['icon_path', 'header_path']) && isset($data['preview_url'])) {
                    $result[$field] = $data['preview_url'];
                }
            }
        }

        return $result;
    }
}
