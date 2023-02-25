<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RegistratedCompatitorsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $kata_or_kumite = $this->category->kata_or_kumite ? 'Kate' : 'Kumite';
        $gender = $this->category->gender == 1 ? 'M' : ($this->category->gender == 2 ? 'Ž' : 'M + Ž');
        $ekipno = $this->category->solo_or_team == 0 ? ' | Ekipno' : null;
        $price = $this->category->solo_or_team == 0 ? $this->compatition->price_single : $this->compatition->price_team;
        if($this->category->solo_or_team == 0 && $this->team_id !== null){
            $name = 'team';
            $data = [
                'id' => $this->team->id,
                'name' => $this->team->name
            ];
        } else {
            $name = 'single';
            $data = true;
        }
        return [
            'registrationId' => $this->id,
            'compatitor' => [
                'id' => $this->compatitor->id,
                'kscgId' => $this->compatitor->kscg_compatitor_id,
                'name' => $this->compatitor->name,
                'lastName' => $this->compatitor->last_name,
            ],
            'status' => $this->status,
            'price' => $price,
            'category' => $kata_or_kumite . ' | ' . $gender . ' | ' . $this->category->name . ' ' . $this->category->category_name  . $ekipno,
            $name => $data
        ];
    }
}
