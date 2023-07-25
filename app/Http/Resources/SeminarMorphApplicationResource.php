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
        if($this->applicable_type == 'App\Models\Compatitor') {
            $user = new CompatitorsResource($this->applicable);
        }
        if($this->applicable_type == 'App\Models\SpecialPersonal') {
            $user = new SpecialPersonalsResource($this->applicable);
        }
        return [
            'id' => (string)$this->id,
            'userType' => $this->applicable_type,
            'seminarName' => $this->seminar->name,
            'userData' => $user
        ];
    }
}
