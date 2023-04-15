<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
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
            'kataOrKumite' => ['required', 'boolean'],
            'categoryName' => [ 'string', 'max:255'],
            'gender' => ['required', 'integer', 'min:1', 'max:3'],
            'dateFrom' => ['required', 'date'],
            'dateTo' => ['required', 'date'],
            'weightFrom' => ['required', 'numeric'],
            'weightTo' => ['required', 'numeric'],
            'matchLenght' => ['required', 'numeric'], 
            'soloOrTeam' => ['required', 'boolean']
        ];
    }
}
