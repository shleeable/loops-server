<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AccountService;
use App\Services\AppleAuthService;
use App\Services\SnowflakeService;
use App\Services\StarterKitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Lcobucci\JWT\Encoding\CannotDecodeContent;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\InvalidTokenStructure;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Token\UnsupportedHeaderFound;
use Lcobucci\JWT\UnencryptedToken;

class AppleAuthController extends Controller
{
    public function handle(Request $request)
    {
        abort_unless(config('services.apple.bundle_id') && config('services.apple.team_id') && config('services.apple.key_id'), 404);

        $request->validate([
            'identity_token' => 'required|string',
            'user_id' => 'required|string',
        ]);

        $payload = $this->verifyAppleToken($request->identity_token);

        if (! $payload || $payload['sub'] !== $request->user_id) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        $email = $request->email ?? $payload['email'] ?? null;

        $user = User::where('apple_id', $request->user_id)->first();

        abort_if($user->status === 6, 403, 'Your account has been suspended');

        if (! $user && $email) {
            $user = User::where('email', $email)->first();

            abort_if($user->status === 6, 403, 'Your account has been suspended');

            if ($user) {
                $user->update(['apple_id' => $request->user_id]);
            }
        }

        if ($user && in_array($user->status, [7, 8])) {
            $this->onboardDeactivatedAccount($user);
        }

        $username = 'appleuser'.app(SnowflakeService::class)->next();

        if (! $user) {
            $user = User::create([
                'username' => $username,
                'apple_id' => $request->user_id,
                'email' => $email,
                'name' => $this->formatName($request->full_name),
                'password' => Hash::make(Str::random(50)),
                'register_ip' => request()->ip(),
                'last_ip' => request()->ip(),
                'email_verified_at' => now(),
            ]);
        }

        if (! $user->apple_refresh_token && $request->authorization_code) {
            $user->update([
                'apple_refresh_token' => $this->exchangeForRefreshToken($request->authorization_code),
            ]);
        }

        return response()->json([
            'token' => $user->createToken('Loops for iOS')->accessToken,
            'user' => $user,
        ]);
    }

    private function onboardDeactivatedAccount($user)
    {
        DB::transaction(function () use ($user) {
            $user->update(['status' => 1, 'delete_after' => null]);
            $user->profile->update(['status' => 1]);
            $user->videos()->whereIn('status', [7, 8])->update(['status' => 2]);
            $user->videos()->whereIn('status', [9])->update(['status' => 1]);
            $user->comments()->whereIn('status', ['account_pending_deletion', 'account_disabled'])->update(['status' => 'active']);
            $user->commentReplies()->whereIn('status', ['account_pending_deletion', 'account_disabled'])->update(['status' => 'active']);
            $user->actorNotifications()->whereIn('actor_state', [7, 8])->update(['actor_state' => 1]);
            $user->starterKits()->update(['status' => DB::raw('previous_status')]);
        });
        app(StarterKitService::class)->flushStatsAndPopular();
        AccountService::del($user->profile_id);
        AccountService::get($user->profile_id);
    }

    private function verifyAppleToken(string $token): ?array
    {
        $keys = app(AppleAuthService::class)->getApplePublicKeys();

        $parser = new Parser(new JoseEncoder);
        try {
            $parsed = $parser->parse($token);
        } catch (CannotDecodeContent|InvalidTokenStructure|UnsupportedHeaderFound $e) {
            return null;
        }

        assert($parsed instanceof UnencryptedToken);

        $kid = $parsed->headers()->get('kid');

        $appleKey = collect($keys)->firstWhere('kid', $kid);

        if (! $appleKey) {
            return null;
        }

        $publicKey = $this->buildPublicKey($appleKey);

        $claims = $parsed->claims()->all();

        if ($claims['iss'] !== 'https://appleid.apple.com') {
            return null;
        }
        if ($claims['aud'] !== config('services.apple.bundle_id')) {
            return null;
        }
        if ($claims['exp'] < time()) {
            return null;
        }

        return $claims;
    }

    private function buildPublicKey(array $key): string
    {
        $rsa = \openssl_pkey_get_public([
            'n' => $key['n'],
            'e' => $key['e'],
        ]);

        return \openssl_pkey_get_details($rsa)['key'];
    }

    private function formatName(?array $fullName): ?string
    {
        if (! $fullName) {
            return null;
        }

        return trim(($fullName['givenName'] ?? '').' '.($fullName['familyName'] ?? ''));
    }

    private function exchangeForRefreshToken(string $authorizationCode): ?string
    {
        $response = Http::asForm()->post('https://appleid.apple.com/auth/token', [
            'client_id' => config('services.apple.bundle_id'),
            'client_secret' => app(AppleAuthService::class)->generateClientSecret(),
            'code' => $authorizationCode,
            'grant_type' => 'authorization_code',
        ]);

        return $response->json('refresh_token');
    }
}
