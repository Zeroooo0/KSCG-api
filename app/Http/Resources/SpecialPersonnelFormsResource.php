<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SpecialPersonnelFormsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name_of_parent' => $this->nameOfParent,
            'jmbg' => $this->jmbg,
            'birthDate' => $this->birth_date,
            'birthPlace' => $this->birth_place,
            'address' => $this->address,
            'landlinePhone' => $this->landline_phone,
            'belt' => $this->belt,
            'beltAcquired' => $this->belt_acquired,
            'certificate' => $this->certificate,
            'certificateId' => $this->certificate_id,
            'certificateAcquired' => $this->certificate_acquired,
            'certificateIssuer' => $this->certificate_issuer,
            'policyConfirmation'=> (boolean)$this->policy_confirmation,
            'judgeTitle' => $this->judge_title,
            'judgeTitleAcquired' => $this->judge_title_acquired,
            'forCategories' => $this->for_categories,
        ];
    }
}
