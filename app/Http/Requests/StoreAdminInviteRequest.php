<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminInviteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (! $this->user()) {
            return false;
        }

        return $this->user()->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:80',
            'message' => 'sometimes|nullable|string|max:500',
            'admin_note' => 'sometimes|nullable|string|max:2000',
            'autofollow_accounts' => 'sometimes|array',
            'autofollow_accounts.*' => 'sometimes|exists:profiles,id',
            'verify_email' => 'sometimes|boolean',
            'email_admin_on_join' => 'sometimes|boolean',
            'max_uses' => 'integer|min:0|max:1000000',
            'is_active' => 'sometimes|boolean',
            'expires_at' => 'sometimes|nullable|date',
        ];
    }
}
