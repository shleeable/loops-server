<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\Traits\ApiHelpers;
use App\Services\ConfigService;
use App\Services\UserAuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountSettingsController extends Controller
{
    use ApiHelpers;

    public function __construct(
        protected UserAuditLogService $auditService
    ) {
        $this->middleware('auth');
        $this->auditService = $auditService;
    }

    public function getSharingSettings(Request $request): JsonResponse
    {
        abort_unless(app(ConfigService::class)->atomFeeds(), 404);

        $user = $request->user();

        $res = [
            'has_atom' => $user->has_atom,
            'atom_url' => $user->atomUrl(),
        ];

        return $this->data($res);
    }

    public function updateSharingSettings(Request $request): JsonResponse
    {
        abort_unless(app(ConfigService::class)->atomFeeds(), 404);

        $validated = $request->validate([
            'has_atom' => 'required|boolean',
        ]);

        $user = $request->user();

        if ($validated['has_atom']) {
            $this->auditService->logAtomFeedEnabled($user);
        } else {
            $this->auditService->logAtomFeedDisabled($user);
        }

        $user->update($validated);

        $res = [
            'has_atom' => $user->has_atom,
            'atom_url' => $user->atomUrl(),
        ];

        return $this->data($res);
    }
}
