<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AccountService;
use App\Services\AccountSwitcherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountSwitcherController extends Controller
{
    public function __construct(
        protected AccountSwitcherService $accountSwitcher,
    ) {
        $this->middleware('auth');
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'accounts' => $this->accountSwitcher->getLinkedAccounts(),
            'max' => AccountSwitcherService::MAX_LINKED_ACCOUNTS,
            'at_capacity' => $this->accountSwitcher->isAtCapacity(),
        ]);
    }

    public function switch(Request $request): JsonResponse
    {
        $request->validate([
            'account_id' => ['required'],
        ]);

        $targetId = (int) $request->input('account_id');

        if ($targetId === (int) Auth::id()) {
            return response()->json([
                'success' => true,
                'active_id' => $targetId,
            ]);
        }

        $result = $this->accountSwitcher->switchTo($targetId);

        if (! $result['success']) {
            $messages = [
                'not_linked' => 'That account isn\'t linked on this device anymore. Sign in to add it again.',
                'not_found' => 'That account no longer exists.',
                'disabled' => 'That account has been disabled.',
            ];

            return response()->json([
                'success' => false,
                'reason' => $result['reason'],
                'message' => $messages[$result['reason']] ?? 'That account is no longer available to switch to.',
            ], 422);
        }

        $uid = $request->user()->id;

        $accountData = AccountService::getByUserId($uid, true, true);

        if (empty($accountData)) {
            return response()->json([
                'success' => false,
                'reason' => 'disabled',
                'message' => 'That account is no longer available to switch to.',
            ], 422);
        }

        $accountData['id'] = (string) $uid;

        return response()->json([
            'success' => true,
            'active_id' => (string) Auth::id(),
            'user' => $accountData,
        ]);
    }

    public function remove(Request $request): JsonResponse
    {
        $request->validate([
            'account_id' => ['required'],
        ]);

        $targetId = (int) $request->input('account_id');
        $linked = $this->accountSwitcher->getLinkedIds();

        if (! in_array($targetId, $linked, true)) {
            return response()->json([
                'success' => false,
                'message' => 'That account is not currently linked.',
            ], 422);
        }

        $result = $this->accountSwitcher->removeFromList($targetId);

        return response()->json([
            'success' => true,
            'logged_in' => $result['logged_in'],
            'active_id' => $result['active_id'],
            'linked' => $this->accountSwitcher->getLinkedAccounts(),
        ]);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $this->accountSwitcher->logoutAll();

        return response()->json([
            'success' => true,
            'redirect' => '/',
        ]);
    }
}
