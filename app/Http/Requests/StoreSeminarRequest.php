<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSeminarRequest extends FormRequest
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
            'name' => ['required', 'string'],
            'deadline' => ['required', 'date'],
            'start' => ['required', 'date'],
            'address' => ['required', 'string'],
            'seminarType' => ['required', 'in:licenceSeminar,educationSeminar'],
            'hasJudge' => ['required', 'boolean'],
            'hasCompetitor' => ['required', 'boolean'],
            'hasCoach' => ['required', 'boolean'],
            'priceJudge' => ['required', 'numeric'],
            'priceCompatitor' => ['required', 'numeric'],
            'priceCoach' => ['required', 'numeric'],
            'isHidden' => ['required', 'boolean'],
        ];
    }
}
