<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkBeltsStoreRequest extends FormRequest
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
            '*.name' => ['required', 'string'],
            '*.hashColor' => ['string']
        ];
    }
    protected function prepareForValidation() {
        $data = [];
        foreach($this->toArray() as $obj) {
            $obj['hashColor'] = $obj['hashColor'] ?? null;
            $data[] = $obj;
        }
        

        $this->merge($data);
        
    }
}
