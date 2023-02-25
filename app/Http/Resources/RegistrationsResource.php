<?php

namespace App\Http\Resources;

use App\Models\Category;
use App\Models\Club;
use App\Models\Compatition;
use App\Models\Compatitor;
use Illuminate\Http\Resources\Json\JsonResource;

class RegistrationsResource extends JsonResource
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

        return [
            'id' => $this->id,
            'status' => $this->status,
            'compatition' => [
                'id' => $this->compatition->id,
                'name' => $this->compatition->name,
                'date' => $this->compatition->start_time_date,
                'price' => $price
            ],
            'club' => [
                'id' => $this->club->id,
                'name' => $this->club->name,
                'shortName' => $this->club->short_name,
            ],
            'category' => [
                'id' => $this->category->id,
                'name' => $kata_or_kumite . ' | ' . $gender . ' | ' . $this->category->name . ' ' . $this->category->category_name  . $ekipno,
            ],
            'compatitor' => [
                'id' => $this->compatitor->id,
                'kscgId' => $this->compatitor->kscg_compatitor_id,
                'name' => $this->compatitor->name,
                'lastName' => $this->compatitor->last_name,
            ]
        ];
    }
}
