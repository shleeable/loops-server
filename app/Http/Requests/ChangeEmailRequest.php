<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! $this->user()) {
            return false;
        }

        if ($this->user()->profile->status != 1) {
            return false;
        }

        return true;
    }

    public function rules()
    {
        return [
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                'different:current_email',
            ],
            'password' => 'required|string|min:1',
        ];
    }

    public function messages()
    {
        return [
            'email.different' => 'The new email must be different from your current email.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Current password is required to change your email.',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'current_email' => $this->user()->email,
        ]);
    }
}
