<?php

namespace App\Http\Requests;

use App\Models\CommentReply;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentReplyUpdateRequest extends FormRequest
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
        $comment = CommentReply::whereVideoId($this->route('id'))->findOrFail($cid);

        if ($comment->comment_id != $this->input('parent_id')) {
            return false;
        }

        return $this->user()->can('update', [CommentReply::class, $comment]);
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
            'parent_id' => 'required',
        ];
    }
}
