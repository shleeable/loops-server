<?php

namespace App\Http\Requests;

use App\Models\Comment;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (! $this->user() || ! $this->user()->can_comment) {
            return false;
        }
        $cid = $this->input('id');
        $comment = Comment::whereVideoId($this->route('id'))->findOrFail($cid);

        return $this->user()->can('update', [Comment::class, $comment]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required',
            'comment' => 'required|string|min:1|max:500',
        ];
    }
}
