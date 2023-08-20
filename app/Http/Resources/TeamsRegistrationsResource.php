<?php

namespace App\Http\Resources;

use App\Models\Compatition;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamsRegistrationsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    { 
        $registrations = null;
        if($request->has('competitionId'))
        {
            $registrations = RegistratedCompatitorInTeamResource::collection($this->registrations->where('compatition_id',$request->competitionId));
            $teamPrice = Compatition::where('id', $request->competitionId)->first()->price_team;
        }

        return [
            'id'=> $this->id,
            'name'=> $this->name,
            'price' => $teamPrice,
            'teamMembersCount' => $registrations->count(),
            'teamMembers' => $registrations,
        ];
    }
}
