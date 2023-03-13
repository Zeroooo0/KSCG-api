<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegistrationRequest extends FormRequest
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
            '*.competitionId' => ['required', 'integer'],
            '*.clubId' => ['integer'],
            '*.competitorId' => ['required', 'integer'],
            '*.categoryId' => ['required', 'integer'],
            '*.teamId' => ['integer', 'nullable']
        ];
    }

    protected function prepareForValidation() {
        $data = [];
        foreach($this->toArray() as $obj) {
            $obj['teamId'] = $obj['teamId'] ?? null;

            $data[] = $obj;
        }
        

        $this->merge($data);
        
    }
}
