<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        return response()->json(['success' => false, 'error' => 'Invalid code', 'force_relogin' => false]);
    }
}
