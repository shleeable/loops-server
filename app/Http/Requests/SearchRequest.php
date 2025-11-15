<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (! $this->user()) {
            return false;
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
            'q' => [
                'required',
                'string',
                'min:1',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (preg_match('/^@?[a-zA-Z0-9._-]+$/', $value)) {
                        return;
                    }

                    if (preg_match('/^@?[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $value)) {
                        return;
                    }

                    if (filter_var($value, FILTER_VALIDATE_URL)) {
                        $parsed = parse_url($value);

                        if (! isset($parsed['scheme']) || ! in_array($parsed['scheme'], ['https', 'http'])) {
                            $fail('The query must be a valid username, mention, or URL.');

                            return;
                        }

                        return;
                    }

                    $fail('The query must be a valid username (e.g., username, @username@domain.com, or https://domain.com/user).');
                },
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'q.required' => 'A search query is required.',
            'q.min' => 'The search query must be at least 1 character.',
            'q.max' => 'The search query must not exceed 255 characters.',
        ];
    }
}
