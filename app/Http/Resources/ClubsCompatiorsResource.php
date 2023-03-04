<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClubsCompatiorsResource extends JsonResource
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
            'id' => (string)$this->id,
            'name' => $this->name,
            'lastName' => $this->last_name,        
            'status' => (boolean)$this->status,
            'belt' => $this->belt,
            'brthDay' => date($this->date_of_birth),
            'weight' => $this->weight
        ];
    }
}
