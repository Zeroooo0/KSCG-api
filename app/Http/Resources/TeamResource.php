<?php

namespace App\Http\Resources;

use App\Models\Club;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $clubId = $this->registrations != null ? $this->registrations->first()->club_id : null;
        $club = $clubId != null ? Club::where('id', $clubId)->first() : null;
        $shortName = $club != null ? $club->short_name : null;
        $teamsReg = str_contains($request->embed, 'teamsReg') ? RegistrationsResource::collection($this->registrations) : 'emeddable';
        return [
            'id' => $this->id,
            'name' => $this->name . " ($shortName)",
            'teamsReg' => $teamsReg
        ];
    }
}
