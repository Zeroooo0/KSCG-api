<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SeminarResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $typeSeminar = $this->seminar_type == 'licenceSeminar' ? 'Seminar za licenciranje' : 'Edukativni seminar';
        return [
            'id' => (string)$this->id,
            'name' => $this->name,
            'nameForSeminarType' => $typeSeminar,
            'address' => $this->address,
            'deadline' => $this->deadline,
            'start' => $this->start,
            'seminarType' => $this->seminar_type,
            'hasJudge' => (boolean)$this->has_judge,
            'hasCompetitor' => (boolean)$this->has_compatitor,
            'hasCoach' => (boolean)$this->has_coach,
            'priceJudge' => $this->price_judge,
            'priceCompatitor' => $this->price_compatitor,
            'priceCoach' => $this->price_coach,
            'isHidden' => (boolean)$this->is_hidden,
        ];
    }
}
