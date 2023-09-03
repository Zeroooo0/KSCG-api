<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SeminarMorphApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = null;
        $userType = null;
        if($this->applicable_type == 'App\Models\Compatitor') {
            $user = new CompatitorsResource($this->applicable);
            $userType = 'Takmičar';
        }
        if($this->applicable_type == 'App\Models\SpecialPersonal') {
            $user = new SpecialPersonalsResource($this->applicable);
            if($this->applicable->role == 1) {
                $userType = 'Sudija';  
            }
            if($this->applicable->role == 2) {
                $userType = 'Trener';  
            }
        }
        return [
            'id' => (string)$this->id,
            'userType' => $userType,
            'seminarName' => $this->seminar->name,
            'userData' => $user
        ];
    }
}
