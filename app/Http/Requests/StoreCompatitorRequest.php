<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
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
            'name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'integer', 'max:3'], Rule::in([1, 2]),
            'jmbg' => ['required', 'numeric'],
            'belt' => ['required', 'integer'],
            'dateOfBirth' => ['required', 'date'],
            'weight' => ['required', 'numeric'],
            'image' => ['image', 'mimes:jpg,jpeg,svg,gif,png', 'max:2048'],
            'clubId' => ['integer'],
            'status' => ['boolean'],
            'document' => [ 'mimes:doc,docx,pdf,xml,html,jpg,jpeg,svg,gif,png', 'max:20480']
        ];
    }
}
