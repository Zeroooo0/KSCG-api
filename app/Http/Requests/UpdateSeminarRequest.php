<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSeminarRequest extends FormRequest
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
            'name' => ['string'],
            'deadline' => ['date'],
            'start' => ['date'],
            'country' => ['string'],
            'city' => ['string'],
            'address' => ['string'],
            'host' => ['string'],
            'seminarType' => ['in:licenceSeminar,educationSeminar'],
            'hasJudge' => ['boolean'],
            'hasCompetitor' => ['boolean'],
            'hasCoach' => ['boolean'],
            'priceJudge' => ['numeric'],
            'priceCompatitor' => ['numeric'],
            'priceCoach' => ['numeric'],
            'isHidden' => ['boolean'],
            'image' => ['image', 'mimes:jpg,jpeg,svg,gif,png', 'max:2048'],
        ];
    }
}
