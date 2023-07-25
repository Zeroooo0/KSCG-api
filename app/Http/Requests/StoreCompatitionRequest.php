<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCompatitionRequest extends FormRequest
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
            'hostName' => ['required', 'string', 'max:255'],
            'startTimeDate' => ['required', 'date'],
            'registrationDeadline' => ['required', 'date'],
            'priceSingle' => ['required', 'numeric'],
            'priceTeam' => ['required', 'numeric'],
            'country' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'status' => ['boolean', 'max:255'],
            'categories' =>['required', 'string'],
            'tatamiNo' =>['required', 'numeric'],
            'applicationLimits' => ['required', 'integer', 'max:3'], Rule::in([1, 2]),
            'categoryStartPoint' => ['required', 'integer', 'max:3'], Rule::in([1, 2]),
            'image' => ['image', 'mimes:jpg,jpeg,svg,gif,png', 'max:20480'],
            'document' => ['mimes:doc,docx,pdf,xml,html', 'max:20480' ],
            'isAbroad' => ['boolean'],
            'rematch' => ['boolean'],
        ];
    }
}
