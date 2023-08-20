<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RegistratedCompatitorInTeamResource extends JsonResource
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

        $competitorData = null;
        if($this->compatitor != null) {
            $competitorData = [
                'id' => $this->compatitor != null ? (string)$this->compatitor->id : null,
                'kscgId' => $this->compatitor->kscg_compatitor_id,
                'name' => $this->compatitor->name,
                'lastName' => $this->compatitor->last_name,
            ];
        }
        return [
            'registrationId' => (string)$this->id,
            'competitor' => $competitorData,
            'category' => $kata_or_kumite . ' | ' . $gender . ' | ' . $this->category->name . ' ' . $this->category->category_name  . $ekipno,

        ];
    }
}
