<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCompatitionRequest extends FormRequest
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
            'hostName' => ['string', 'max:255'],
            'startTimeDate' => ['date'],
            'registrationDeadline' => ['date'],
            'priceSingle' => ['numeric'],
            'priceTeam' => ['numeric'],
            'country' => ['string', 'max:255'],
            'city' => ['string', 'max:255'],
            'address' => ['string', 'max:255'],
            'status' => ['boolean'],
            'applicationLimits' => [ 'integer', 'max:3'], Rule::in([1, 2]),
            'registrationStatus' => ['boolean'],
            'categories' => ['string', 'max:255'],
        ];
    }
}
