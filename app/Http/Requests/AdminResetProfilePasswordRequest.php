<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class AdminResetProfilePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) ($this->user()->is_admin ?? false);
    }

    public function rules(): array
    {
        return [
            'password' => [
                'required',
                'string',
                Password::min(12)
                    ->max(64)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'send_email' => ['sometimes', 'boolean'],
            'force_change' => ['sometimes', 'boolean'],
            'revoke_sessions' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.uncompromised' => 'This password has appeared in a known data breach. Please generate another.',
        ];
    }

    /**
     * Rate limit password-reset attempts per admin to make brute-force
     * / accidental mass-reset situations harder.
     */
    protected function passesAuthorization()
    {
        $passed = parent::passesAuthorization();

        if ($passed && $this->user()) {
            $key = 'admin:reset-pw:'.$this->user()->id;
            $limiter = app(\Illuminate\Cache\RateLimiter::class);

            if ($limiter->tooManyAttempts($key, maxAttempts: 20)) {
                abort(429, 'Too many password resets. Slow down.');
            }

            $limiter->hit($key, decaySeconds: 300);
        }

        return $passed;
    }
}
