<?php

namespace App\Http\Resources;

use App\Models\CuratedApplication;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CuratedApplication
 */
class CuratedApplicationResource extends JsonResource
{
    public function __construct(CuratedApplication $resource)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $user = $this->user_id ? AccountService::getByUserId($this->user_id, true, true) : [];

        return [
            'id' => (string) $this->id,
            'email' => $this->email,
            'username_requested' => $this->username_requested,
            'age_at_submission' => $this->age_at_submission,
            'reason' => $this->reason,
            'fediverse_account' => $this->fediverse_account,
            'custom_answers' => $this->custom_answers,
            'status' => $this->status,
            'status_reason' => $this->status_reason,
            'email_verified' => $this->isEmailVerified(),
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'reviewer' => $this->whenLoaded('reviewer', fn () => [
                'id' => $this->reviewer->id,
                'username' => $this->reviewer->username,
            ]),
            'notes' => $this->whenLoaded('notes', fn () => $this->notes->map(function ($note) {
                $acct = AccountService::getByUserId($note->admin_id, true, true);

                $res = [
                    'id' => (string) $note->id,
                    'body' => $note->body,
                    'created_at' => $note->created_at->toIso8601String(),
                ];

                $res['admin'] = $acct;

                return $res;
            })),
            'custom_questions' => $this->whenHas('custom_questions'),
            'custom_rejections' => $this->whenHas('custom_rejections'),
            'notes_count' => $this->whenCounted('notes'),
            'user_id' => $this->user_id,
            'user' => $user,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
