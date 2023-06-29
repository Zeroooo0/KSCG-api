<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
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
            'kataOrKumite' => ['boolean'],
            'categoryName' => ['string', 'max:255'],
            'gender' => ['integer', 'min:1', 'max:3'],
            'dateFrom' => ['required', 'date'],
            'dateTo' => ['required', 'date'],
            'matchLenght' => ['numeric'], 
            'yearsFrom' => ['numeric', 'gte:0','nullable'], 
            'yearsTo' => ['numeric', 'gte:0','nullable'], 
            'rematch' => ['boolean'], 
            'soloOrTeam' => ['boolean'],
            'status' => ['boolean'],
        ];
    }
}
