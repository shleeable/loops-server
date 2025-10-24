<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\HCaptchaRule;
use App\Rules\TurnstileRule;
use App\Services\CaptchaService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
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
     * Show the application's login form.
     * Override to capture OAuth parameters.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm(Request $request)
    {
        if ($request->has(['client_id', 'redirect_uri', 'response_type'])) {
            Session::put('oauth_request', [
                'client_id' => $request->input('client_id'),
                'redirect_uri' => $request->input('redirect_uri'),
                'response_type' => $request->input('response_type'),
                'scope' => $request->input('scope', ''),
                'state' => $request->input('state', ''),
            ]);
        }

        return view('auth.login');
    }

    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        $isOAuthRedirect = $request->input('oauth_redirect') === 'true';

        if ($isOAuthRedirect) {
            $oauthParams = [
                'client_id' => $request->input('client_id'),
                'redirect_uri' => $request->input('redirect_uri'),
                'response_type' => $request->input('response_type'),
                'scope' => $request->input('scope'),
                'state' => $request->input('state'),
            ];
        }

        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($isOAuthRedirect) {
            return redirect()->route('login', array_filter($oauthParams));
        }

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect('/');
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

    /**
     * The user has been authenticated.
     * Override to handle OAuth authorization flow.
     *
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        if (Session::has('oauth_request')) {
            $oauthParams = Session::pull('oauth_request');

            return redirect('/oauth/authorize?'.http_build_query(array_filter($oauthParams)));
        }

    }
}
