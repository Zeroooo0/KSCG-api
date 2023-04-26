<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title' => ['required', 'string'],
            'slug' => ['string', 'not_regex:/([A-Z])| |\.|\/n/'],
            'content' => ['string'],
            'excerpt' => ['string'],
            'gallery' => ['boolean'],
            'image' => ['image', 'mimes:jpg,jpeg,svg,gif,png', 'max:20480'],
        ];
    }
}
