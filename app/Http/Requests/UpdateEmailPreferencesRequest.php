<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailPreferencesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user() &&
               $this->user()->profile->status === 1 &&
               $this->user()->profile_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email_notifications' => 'required|boolean',
            'product_updates' => 'required|boolean',
            'marketing_emails' => 'required|boolean',
            'email_frequency' => 'required|in:instant,daily,weekly,monthly',
        ];
    }
}
