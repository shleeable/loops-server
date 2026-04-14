<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\ApiHelpers;
use App\Http\Controllers\Controller;
use App\Services\PushTokenCacheService;
use Illuminate\Http\Request;
use Laravel\Passport\Token;

class AppController extends Controller
{
    use ApiHelpers;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function handleLogout(Request $request)
    {
        $user = $request->user();

        app(PushTokenCacheService::class)->remove((string) $user->profile_id);

        $user->update(['push_token' => null, 'push_token_platform' => null, 'push_token_verified_at' => null]);

        $user->tokens()->each(function (Token $token) {
            $token->revoke();
            $token->refreshToken?->revoke();
        });

        return response()->json(['message' => 'Successfully logged out.']);
    }
}
