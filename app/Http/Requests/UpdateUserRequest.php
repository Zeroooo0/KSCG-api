<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class UpdateUserRequest extends FormRequest
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
            'last_name' => ['string', 'max:255'],
            'email' => ['string', 'max:255'],
            'password' => [ 'min:6','required_with:passwordConfirmation', 'same:passwordConfirmation', Rules\Password::defaults()],
            'passwordConfirmation' => ['min:6'],
            'user_type' => ['integer', 'max:4']
        ];
    }
}
