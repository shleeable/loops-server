<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetMentionAutocomplete extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can_upload;
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
                'min:2',
                'max:90',
                'regex:/^[A-Za-z0-9.\-_@]+$/',
            ],
            'limit' => 'sometimes|int|min:1|max:10',
        ];
    }
}
