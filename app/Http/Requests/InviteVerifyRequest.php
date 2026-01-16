<?php

namespace App\Http\Requests;

use App\Rules\HCaptchaRule;
use App\Rules\TurnstileRule;
use App\Services\CaptchaService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;

class InviteVerifyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->user()) {
            throw new AuthorizationException('You are already logged in, you must logout before registering a new account.');
        }

        if (config('mail.default') == 'log' && app()->environment() === 'production') {
            throw new AuthorizationException('Mail service not configured, please contact support for assistance.');
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $hasCaptcha = config('captcha.enabled');
        $rules = [
            'token' => 'required|string|min:40|max:46',
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

        return $rules;
    }
}
