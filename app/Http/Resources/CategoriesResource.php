<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoriesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $kata_or_kumite = $this->kata_or_kumite ? 'Kate' : 'Kumite';
        $gender = $this->gender == 1 ? 'M' : ($this->gender == 2 ? 'Ž' : 'M + Ž');
        $soloOrTeam = $this->solo_or_team == 0 ? 'Pojedinačno' : 'Ekipno';
        $ekipno = $this->solo_or_team == 0 ? ' | Ekipno' : null;
        $belts = [];
        foreach ($this->belts as $belt) {
            $belts[] = $belt->id;
        }
        return [
            'id' => $this->id,
            'combinedName' => $kata_or_kumite . ' | ' . $gender . ' | ' . $this->name . ' ' . $this->category_name  . $ekipno,
            'name' => $this->name,
            'kataOrKumite' => $kata_or_kumite,
            'categoryName' => $this->category_name,
            'gender' => $gender, 
            'soloOrTeam' => $soloOrTeam,
            'validationData' => [
                'dateFrom' => $this->date_from,
                'dateTo' => $this->date_to,
                'weightFrom' => $this->weight_from,
                'weightTo' => $this->weight_to,
                'soloOrTeam' => $this->solo_or_team,
                'lenghtOfMatch' => $this->match_lenght,
                'status' => $this->status,
                'belts' => $belts
            ]
        ];
    }
}
