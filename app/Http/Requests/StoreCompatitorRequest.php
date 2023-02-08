<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompatitorRequest extends FormRequest
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
            'kscgId' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'integer', 'max:3'],
            'jmbg' => ['required', 'integer'],
            'belt' => ['required', 'string', 'max:255'],
            'dateOfBirth' => ['required', 'date'],
            'weight' => ['required', 'decimal:2'],
            'image' => ['image', 'mimes:jpg,jpeg,svg,gif.png', 'max:2048'],
            'clubId' => ['integer'],
            'status' => ['boolean']
            
        ];
    }
}
