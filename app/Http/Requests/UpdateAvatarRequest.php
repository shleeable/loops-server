<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvatarRequest extends FormRequest
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
            'avatar' => [
                'required',
                'file',
                'mimes:jpg,png,jpeg',
                'min:1',
                'max:'.(2 * 1024),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.mimes' => 'Avatar must be a JPG, PNG, or JPEG image.',
            'avatar.min' => 'Avatar must be at least 10 KB.',
            'avatar.max' => 'Avatar must be smaller than 2 MB.',
        ];
    }
}
