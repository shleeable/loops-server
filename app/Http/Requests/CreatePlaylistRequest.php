<?php

namespace App\Http\Requests;

use App\Models\Playlist;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class CreatePlaylistRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (! $this->user()) {
            return false;
        }

        $response = Gate::inspect('create', Playlist::class);

        if ($response->denied()) {
            abort(403, 'You are not authorized to create a playlist.'
                .'<br/><br/>This could mean:'
                .'<ul style="list-style: disc; padding-left: 1.25rem; margin-top: 0.5rem;" class="text-sm">'
                .'<li>You haven\'t posted enough videos or don\'t have enough followers yet</li>'
                .'<li>You\'ve reached your playlist limit for your current tier</li>'
                .'<li>Your account has been restricted from this feature</li>'
                .'</ul>'
                .'<br/><p class="text-sm">If you think this is a mistake, please contact support.</p>'
            );
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
            'name' => 'required|string|max:30',
            'description' => 'nullable|string|max:1000',
            'visibility' => 'required|in:public,unlisted,private,followers',
        ];
    }
}
