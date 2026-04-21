<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminSendProfileEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) ($this->user()->is_admin ?? false);
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'min:3', 'max:120'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
            'cc_admin' => ['sometimes', 'boolean'],
            'log_as_audit' => ['sometimes', 'boolean'],
            'template_id' => ['nullable', 'string', 'in:blank,general,tos,security'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject.max' => 'Subject must be 120 characters or fewer.',
            'message.max' => 'Message must be 2000 characters or fewer.',
            'message.min' => 'Message is too short.',
            'template_id.in' => 'Unknown email template.',
        ];
    }
}
