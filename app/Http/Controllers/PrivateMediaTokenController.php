<?php

namespace App\Http\Controllers;

use App\Services\PrivateMediaTokenService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrivateMediaTokenController extends Controller
{
    public function __construct(
        private readonly PrivateMediaTokenService $tokens
    ) {
        $this->middleware('auth');
    }

    public function show(Request $request, string $tokenId): StreamedResponse
    {
        $user = $request->user();
        $token = $this->tokens->resolve($tokenId, $user->profile_id, $user->is_admin);

        return $this->tokens->stream($token);
    }
}
