<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClubRequest extends FormRequest
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
            'name' => ['required', 'string','max:255', 'unique:clubs,name'],
            'shortName' => ['required', 'string','max:255', 'unique:clubs,short_name'],
            'country' => ['required', 'string','max:255'],
            'city' => ['required', 'string','max:255'],
            'address' => ['required', 'string','max:255'],
            'pib' => ['required', 'string'],
            'email' => ['required', 'string', 'max:255'],
            'phoneNumber' => ['required', 'string', 'max:255'],
            'image' => ['image', 'mimes:jpg,jpeg,svg,gif.png', 'max:2048'],
            'userId' => ['integer', 'unique:clubs,user_id'],
            'status' => ['required','boolean']
        ];
    }
}
