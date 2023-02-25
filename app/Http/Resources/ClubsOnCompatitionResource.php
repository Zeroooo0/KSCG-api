<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class ClubsOnCompatitionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $reg_compatitors = RegistratedCompatitorsResource::collection($this->registrations);
        $team_price = 0;
        $single_price = 0;
        foreach ($reg_compatitors as $test=>$val) {
            $team_price = $val->compatition->price_team;
            $single_price = $val->compatition->price_single;
        }
        $registration_single = $this->registrations->where('team_or_single', 1)->count();
        $registration_team = $this->registrations->where('team_or_single', 0)->countBy('team_id')->count();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'totalRegistrationNo' => $reg_compatitors->count(),
            'singleRegistrationNo' => $registration_single,
            'teamRegistrationNo' => $registration_team,
            'totalPrice' => $single_price * $registration_single + $team_price * $registration_team,
            'compatitors' => $reg_compatitors,

        ];
    }
}
