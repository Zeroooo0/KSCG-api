<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ResultsResource extends JsonResource
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
        $position = $this->position;
        $medal = '';
        if(is_null($position)) {
            $medal = 'Registrovan na takmičenje';
        }
        if($position == 0) {
            $medal = 'Učešće';
        }
        if($position == 1) {
            $medal = 'Bronza';
        }
        if($position == 2) {
            $medal = 'Srebro';
        }
        if($position == 3) {
            $medal = 'Zlato';
        }
        return [
            'id' => (string)$this->id,
            'competition' => $this->compatition->name,
            'category' => $kata_or_kumite . ' | ' . $gender . ' | ' . $this->category->name . ' ' . $this->category->category_name  . $ekipno,
            'position' => $medal,
            'date' => Date('Y-m-d', strtotime($this->compatition->start_time_date))
        ];
    }
}
