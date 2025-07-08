<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDataSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user() &&
               $this->user()->profile->status === 1 &&
               $this->user()->profile_id;
    }

    public function rules(): array
    {
        return [
            'data_retention_period' => [
                'required',
                Rule::in(['1year', '2years', '5years', 'never']),
            ],
            'analytics_tracking' => 'required|boolean',
            'research_data_sharing' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'data_retention_period.required' => 'Data retention period is required.',
            'data_retention_period.in' => 'Invalid data retention period selected.',
            'analytics_tracking.required' => 'Analytics tracking preference is required.',
            'analytics_tracking.boolean' => 'Analytics tracking must be true or false.',
            'research_data_sharing.required' => 'Research data sharing preference is required.',
            'research_data_sharing.boolean' => 'Research data sharing must be true or false.',
        ];
    }
}
