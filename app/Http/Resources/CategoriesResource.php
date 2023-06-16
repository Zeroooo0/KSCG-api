<?php

namespace App\Http\Resources;

use App\Traits\LenghtOfCategory;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoriesResource extends JsonResource
{
    use LenghtOfCategory;
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
        $soloOrTeam = $this->solo_or_team ? 'Pojedinačno' : 'Ekipno';
        $ekipno = $this->solo_or_team  ? null : ' | Ekipno';
        $belts = [];
        foreach ($this->belts as $belt) {
            $belts[] = (string)$belt->id;
        }

        return [
            'id' => (string)$this->id,
            'combinedName' => $kata_or_kumite . ' | ' . $gender . ' | ' . $this->name . ' ' . $this->category_name  . $ekipno,
            'name' => $this->name,
            'kataOrKumite' => $this->kata_or_kumite,
            'categoryName' => $this->category_name,
            'gender' => $this->gender, 
            'soloOrTeam' => $soloOrTeam,
            'dateFrom' => date($this->date_from),
            'dateTo' => date($this->date_to),
            'yearsFrom' => $this->years_from,
            'yearsTo' => $this->years_to,
            'dateToPlusYear' => $this->solo_or_team ? date('Y-m-d', strtotime($this->date_to. ' +1 year' )) : date($this->date_to),
            'soloOrTeam' => $this->solo_or_team,
            'rematch' => (boolean)$this->repesaz,
            'lenghtOfMatch' => $this->match_lenght,
            'status' => (boolean)$this->status,
            'belts' => $belts,
            
        ];
    }
}
