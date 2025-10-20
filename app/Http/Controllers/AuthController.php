<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    public function verifyTwoFactor(Request $request)
    {
        $request->validate(['otp_code' => 'required|digits:6']);
        $user = User::find(session('2fa:user:id'));

        if (! $user) {
            return response()->json(['success' => false, 'error' => 'Invalid session.', 'force_relogin' => false]);
        }
        $google2fa = new Google2FA;
        $secret = $user->two_factor_secret;
        $attempts = session('2fa:user:attempts');
        if ($attempts >= 3) {
            session()->forget('2fa:user:id');

            return response()->json(['success' => false, 'error' => 'Too many attempts', 'force_relogin' => true]);
        }
        $request->session()->increment('2fa:user:attempts');
        if ($google2fa->verifyKey($secret, $request->otp_code)) {
            Auth::login($user, session()->pull('2fa:remember', false));
            session()->forget('2fa:user:id');

            return response()->json(['success' => true, 'error' => null]);
        }

        return response()->json(['success' => false, 'error' => 'Invalid code, please try again.', 'force_relogin' => false]);
    }

    public function registerApp(Request $request)
    {
        $data = $request->validate([
            'client_name' => ['required', 'string'],
            'redirect_uris' => ['required', 'array', 'min:1'],
            'redirect_uris.*' => ['required', 'string'],
        ]);

        $uris = collect($data['redirect_uris'])
            ->map('urldecode')
            ->filter()
            ->join(',');

        $client = Passport::client()->forceFill([
            'name' => e($request->client_name),
            'secret' => Str::random(40),
            'grant_types' => ['authorization_code', 'refresh_token'],
            'redirect_uris' => [$uris],
            'revoked' => false,
        ]);

        $client->save();

        return response()->json([
            'client_id' => (string) $client->id,
            'client_name' => $client->name,
            'client_secret' => $client->plainSecret ?? null,
            'redirect_uris' => $data['redirect_uris'],
            'vapid_key' => null,
        ]);
    }
}
