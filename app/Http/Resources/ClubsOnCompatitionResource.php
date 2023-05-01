<?php

namespace App\Http\Resources;

use App\Models\Club;
use App\Models\SpecialPersonal;
use App\Models\Team;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

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


        $reg_compatitors = $this->registrations->where('compatition_id', $request->competitionId);
 
        $single_price = 0;
        $team_price = 0;
        foreach ($reg_compatitors as $test=>$val) {
            $team_price = $val->compatition->price_team;
            $single_price = $val->compatition->price_single;
        }
        $registration_single = $reg_compatitors->where('team_or_single', 1);
        $registration_team = $reg_compatitors->where('team_or_single', 0)->groupBy('team_id');
        $totalPrice = $single_price * $registration_single->count() + $team_price * $registration_team->count();
        $teams = [];
        foreach($registration_team as $key=>$val){
            $teams[] = $key;
        }
        $teamCollection = Team::whereIn('id',$teams);
        if(Auth::user() == null){
            $totalPrice = null;
        }
        $roles = $this->roles->where('roleable_type', 'App\Models\Compatition')->where('roleable_id', $request->competitionId);
        $arrayOfRoles = [];
        foreach($roles as $person) {
            $arrayOfRoles[] = $person->special_personals_id;
        }
        $registeredPersonnal = SpecialPersonal::whereIn('id',$arrayOfRoles)->get();

        return [
            'id' => (string)$this->id,
            'name' => $this->name,
            'totalRegistrationNo' => $reg_compatitors->count(),
            'singleRegistrationNo' => $registration_single->count(),
            'teamRegistrationNo' => $registration_team->count(),
            'totalPrice' => $totalPrice,
            'gold' => $reg_compatitors->where('position', 3)->count(), 
            'silver' => $reg_compatitors->where('position', 2)->count(),
            'bronze' => $reg_compatitors->where('position', 1)->count(),
            'points' => $reg_compatitors->sum('position'),
            'officialTrainer' => $registeredPersonnal,
            'teamsList' => $request->has('embed') && str_contains($request->embed, 'teamsList') ? TeamsRegistrationsResource::collection($teamCollection->get()) : 'embedable',
            'singlesList' => $request->has('embed') && str_contains($request->embed, 'singlesList') ? RegistratedCompatitorsSingleResource::collection($registration_single) : 'embedable',
            
        ];
    }
}
