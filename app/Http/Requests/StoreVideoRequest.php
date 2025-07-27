<?php

namespace App\Http\Requests;

use App\Models\Video;
use Cache;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreVideoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (! $this->user() || ! $this->user()->can_upload) {
            return false;
        }

        if (
            Video::whereProfileId($this->user()->profile_id)
                ->where('created_at', '>', now()->subHours(12))
                ->count() >= 8
        ) {
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
        $config = Cache::get('settings:public');
        $maxSize = data_get($config, 'media.max_video_size', 40);

        return [
            'video' => [
                'required',
                File::types(['mp4'])
                    ->min(250)
                    ->max($maxSize * 1024),
            ],
            'description' => 'nullable|string|max:200',
            'comment_state' => 'sometimes|string|in:0,4',
            'can_download' => 'sometimes',
        ];
    }
}
