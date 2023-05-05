<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateComatitorRequest extends FormRequest
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
            'kscgId' => ['string', 'max:255'],
            'name' => ['string', 'max:255'],
            'country' => ['string', 'max:255'],
            'lastName' => ['string', 'max:255'],
            'gender' => ['integer', 'max:3'], Rule::in([1, 2]),
            'jmbg' => ['integer'],
            'belt' => ['integer'],
            'dateOfBirth' => ['date'],
            'image' => ['image', 'mimes:jpg,jpeg,svg,gif,png', 'max:2048'],
            'clubId' => ['integer'],
            'status' => ['boolean']
        ];
    }
}
