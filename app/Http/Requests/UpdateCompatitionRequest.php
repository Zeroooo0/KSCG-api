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
            'name' => [ 'string', 'max:255'],
            'hostName' => ['string', 'max:255'],
            'startTimeDate' => ['date'],
            'registrationDeadline' => ['date'],
            'priceSingle' => [ 'numeric'],
            'priceTeam' => ['numeric'],
            'country' => ['string', 'max:255'],
            'city' => ['string', 'max:255'],
            'address' => ['string', 'max:255'],
            'status' => ['boolean', 'max:255'],
            'categories' =>['string'],
            'tatamiNo' =>['numeric'],
            'applicationLimits' => ['integer', 'max:3'], Rule::in([1, 2]),
            'categoryStartPoint' => ['integer', 'max:3'], Rule::in([0, 1]),
            'image' => ['mimes:jpg,jpeg,svg,gif,png', 'max:20480'],
            'document' => ['mimes:doc,docx,pdf,xml,html', 'max:20480' ],
            'isAbroad' => ['boolean'],
            'rematch' => ['boolean'],
            'type' => ['string', 'in:WKF,EKF,BKF,MKF,KSCG,SSEKF,Turniri'],
        ];
    }
}
