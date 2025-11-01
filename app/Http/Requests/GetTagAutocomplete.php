<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetTagAutocomplete extends FormRequest
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
            'q' => 'required|alpha_dash|min:2|max:60',
            'limit' => 'sometimes|int|min:1|max:10',
        ];
    }
}
