<?php

namespace App\Http\Resources;

use App\Models\AdminAuditLog;
use App\Models\Hashtag;
use App\Models\Instance;
use App\Models\Report;
use App\Models\StarterKit;
use App\Models\Video;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AdminAuditLog
 */
class AdminAuditLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $auditor = AccountService::getByUserId($this->user_id);

        $resource = [];

        if ($this->activity_type && $this->activity_id) {
            $resource = match ($this->activity_type) {
                'App\Models\Profile' => AccountService::compact($this->activity_id),
                'App\Models\Hashtag' => (new AdminHashtagResource(Hashtag::find($this->activity_id))),
                'App\Models\Instance' => (new AdminInstanceResource(Instance::find($this->activity_id))),
                'App\Models\Report' => (new ReportResource(Report::find($this->activity_id))),
                'App\Models\StarterKit' => (new AdminStarterKitResource(StarterKit::find($this->activity_id))),
                'App\Models\Video' => (new VideoResource(Video::find($this->activity_id))),
                'App\Models\User' => AccountService::getByUserId($this->activity_id),
                default => [],
            };
        }

        return [
            'id' => $this->id,
            'auditor' => $auditor,
            'type' => $this->type,
            'value' => $this->value,
            'model' => $this->activity_type,
            'resource' => $resource,
            'created_at' => $this->created_at,
        ];
    }
}
