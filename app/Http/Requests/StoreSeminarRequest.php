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
            'country' => ['required', 'string'],
            'city' => ['required', 'string'],
            'address' => ['required', 'string'],
            'host' => ['required', 'string'],
            'seminarType' => ['required', 'in:licenceSeminar,educationSeminar'],
            'hasJudge' => ['required', 'boolean'],
            'hasCompetitor' => ['required', 'boolean'],
            'hasCoach' => ['required', 'boolean'],
            'priceJudge' => [ 'numeric', 'required_if:hasCoach,1'],
            'priceCompetitor' => ['numeric', 'required_if:hasCoach,1'],
            'priceCoach' => ['numeric', 'required_if:hasCoach,1'],
            'isHidden' => [ 'boolean'],
            'image' => ['image', 'mimes:jpg,jpeg,svg,gif,png', 'max:2048'],
        ];
    }
}
