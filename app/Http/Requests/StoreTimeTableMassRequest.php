<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTimeTableMassRequest extends FormRequest
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
            '*.compatitionId' => ['required', 'integer'],
            '*.categoryId' => ['required', 'integer'],
            '*.tatamiNo' => ['required', 'numeric'],
            '*.orderNo' => ['required', 'numeric'],
        ];
    }
    protected function prepareForValidation() {
        $data = [];
        foreach($this->toArray() as $obj) {
            $data[] = $obj;
        }
        

        $this->merge($data);
        
    }
}
