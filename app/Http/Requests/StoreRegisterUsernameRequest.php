<?php

namespace App\Http\Requests;

use App\Models\AdminSetting;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRegisterUsernameRequest extends FormRequest
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

        if (AdminSetting::where('key', 'general.openRegistration')->whereRaw("JSON_EXTRACT(value, '$') = false")->exists()) {
            throw new AuthorizationException('Registration is disabled, please try again later.');
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
        return [
            'username' => [
                'required',
                'alpha_dash',
                'min:2',
                'max:30',
                'unique:users,username',
                Rule::notIn(['admin', 'dansup', 'support', 'loops', 'official', 'team', 'teamLoops', 'new', 'discover', 'explore']),
            ],
            'password' => 'required|min:8',
            'password_confirmation' => 'required|confirmed:password',
            'birth_date' => 'required|date|before:today',
        ];
    }
}
