<?php

namespace App\Http\Resources;

use App\Models\SpecialPersonal;
use Illuminate\Http\Resources\Json\JsonResource;

class ClubsAdministrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $specPerson = new SpecialPersonalsResource(SpecialPersonal::find($this->special_personals_id));
        $linkView = env('APP_URL') . '/api/v1/special-personal/' . $this->special_personals_id;
        return [
            'name' => $specPerson->name,
            'lastName' => $specPerson->last_name,
            'title' => $this->title,
            'status' => $specPerson->status,
            'personsDetailView' => $linkView
        ];
    }
}
