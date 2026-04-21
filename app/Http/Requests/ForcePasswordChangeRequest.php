<?php

namespace App\Http\Requests;

use App\Http\Controllers\Auth\ForcePasswordChangeController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules\Password;

class ForcePasswordChangeRequest extends FormRequest
{
    /**
     * Only allow users who are mid-pwchange flow. Guarded by session
     * rather than auth middleware because the user isn't logged in yet.
     */
    public function authorize(): bool
    {
        return Session::has(ForcePasswordChangeController::SESSION_USER_ID);
    }

    public function rules(): array
    {
        return [
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(12)
                    ->max(64)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'Please choose a new password.',
            'password.confirmed' => 'The passwords do not match.',
            'password.min' => 'Your password must be at least 12 characters.',
            'password.uncompromised' => 'This password has appeared in a known data breach. Please choose a different one.',
        ];
    }
}
