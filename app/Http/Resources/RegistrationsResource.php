<?php

namespace App\Http\Resources;

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
        $categoryData = null;
        $compatitorData = null;
        $compatitionData = null;
        $clubData = null;
        if ($this->category != null) {
            $kata_or_kumite = $this->category->kata_or_kumite ? 'Kate' : 'Kumite';
            $gender = $this->category->gender == 1 ? 'M' : ($this->category->gender == 2 ? 'Ž' : 'M + Ž');
            $ekipno = $this->category->solo_or_team == 0 ? 'Ekipno' : null;
            $price = $this->category->solo_or_team == 0 ? $this->compatition->price_single : $this->compatition->price_team;
            $categoryData = [
                'id' => (string)$this->category->id,
                'name' => $kata_or_kumite . ' | ' . $gender . ' | ' . $this->category->name . ' ' . $this->category->category_name  . ' | ' .  $ekipno,
                'certificateName' => "$kata_or_kumite $ekipno | " . $this->category->name,
            ];
        }
        if ($this->compatitor != null) {
            $compatitorData = [
                'id' => (string)$this->compatitor->id,
                'kscgId' => $this->compatitor->kscg_compatitor_id,
                'name' => $this->compatitor->name,
                'lastName' => $this->compatitor->last_name,
                'gender' => (string)$this->compatitor->gender,
                'birthDay' => date($this->compatitor->date_of_birth),
                'belt' => new BeltResource($this->compatitor->belt),
            ];
        }
        if ($this->compatition != null) {
            $compatitionData = [
                'id' => (string)$this->compatition->id,
                'name' => $this->compatition->name,
                'date' => $this->compatition->start_time_date,
                'price' => $price
            ];
        }
        if ($this->club != null) {
            $clubData = [
                'id' => (string)$this->club->id,
                'name' => $this->club->name,
                'shortName' => $this->club->short_name,
            ];
        }


        return [
            'id' => (string)$this->id,
            'status' => $this->status,
            'position' => (string)(int)$this->position,
            'isPrinted' => (bool)$this->is_printed,
            'competition' => $compatitionData,
            'club' => $clubData,
            'category' => $categoryData,
            'competitor' => $compatitorData,
            'team' => $this->team_id != null ? new TeamResource($this->team) : 'pojedinacni nastup'
        ];
    }
}
