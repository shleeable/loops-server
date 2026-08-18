<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVideoThumbnailRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! $this->user()) {
            return false;
        }

        if (! $this->user()->can_upload) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'thumbnail' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
                'dimensions:min_width=540,min_height=960,ratio=9/16',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'thumbnail.dimensions' => 'Thumbnail must be a 9:16 image at least 540 x 960.',
            'thumbnail.max' => 'Thumbnail must be under 4MB.',
        ];
    }
}
