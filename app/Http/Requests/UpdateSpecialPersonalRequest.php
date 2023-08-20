<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSpecialPersonalRequest extends FormRequest
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
            'name' => ['string', 'max:255'],
            'lastName' => ['string', 'max:255'],
            'country' => ['string', 'max:255'],
            'email' => ['string', 'max:255'],
            'phone' => ['string', 'max:255'],
            'gender' => ['integer'],
            'image' => ['image', 'mimes:,jpeg,svg,gif.png', 'max:2048']
        ];
    }
}
