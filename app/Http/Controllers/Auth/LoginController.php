<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\HCaptchaRule;
use App\Rules\TurnstileRule;
use App\Services\CaptchaService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Validate the user login request.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $hasCaptcha = config('captcha.enabled');

        $rules = [
            $this->username() => 'required|string',
            'password' => 'required|string',
        ];

        if ($hasCaptcha) {
            $rules['captcha_type'] = ['required', 'in:turnstile,hcaptcha'];

            if (config('captcha.driver') === 'turnstile') {
                $rules['captcha_token'] = [
                    'required',
                    'string',
                    new TurnstileRule(new CaptchaService),
                ];
            } elseif (config('captcha.driver') === 'hcaptcha') {
                $rules['captcha_token'] = [
                    'required',
                    'string',
                    new HCaptchaRule(new CaptchaService),
                ];
            }
        }

        $request->validate($rules);
    }

    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        $user = $this->guard()->user();

        if ($user->has_2fa) {
            Session::put('2fa:user:id', $user->id);
            Session::put('2fa:user:attempts', 0);
            $this->guard()->logout();

            return response()->json(['has_2fa' => true]);
        }

        return $this->authenticated($request, $user)
                ?: redirect()->intended($this->redirectPath());
    }
}
