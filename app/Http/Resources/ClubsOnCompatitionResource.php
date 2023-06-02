<?php

namespace App\Http\Resources;

use App\Models\Compatition;
use App\Models\Roles;
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

        $storage_url = env('APP_URL') . 'api/file/';
        
        if($this->image != null) {
            $path =  $storage_url . $this->image->url;
        } else{
            $path = $storage_url . 'default/default-club.jpg';
        }
        $reg_compatitors = $this->registrations->where('compatition_id', $request->competitionId);
        $competition = Compatition::where('id', $request->competitionId)->first();
        $single_price = 0;
        $team_price = 0;
        foreach ($reg_compatitors as $test=>$val) {
            $team_price = $val->compatition->price_team;
            $single_price = $val->compatition->price_single;
        }
        $registration_single = $reg_compatitors->where('team_or_single', 1)->sortBy('compatitor_id');
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
        $roles = $this->roles;
        $arrayOfRoles = [];
        foreach($roles as $person) {
            $arrayOfRoles[] = $person->special_personals_id;
        }
        $dateNow = date('d/m/Y',strtotime($competition->start_time_date));
        $singleRegistrations = $reg_compatitors->where('team_or_single', 1)->where('status', 1);
        $teamRegistrations = $reg_compatitors->where('team_or_single', 0)->where('status', 1);
        
        $gold = [];
        $silver = [];
        $bronze = [];
        foreach($teamRegistrations->groupBy('team_id') as $teamReg) {
            $gold[] = $teamReg->where('position', 3)->groupBy('team_id')->count();
            $silver[] = $teamReg->where('position', 2)->groupBy('team_id')->count();
            $bronze[] = $teamReg->where('position', 1)->groupBy('team_id')->count();
        }
        $pointsTeam = array_sum($gold) * 3 + array_sum($silver) * 2 + array_sum($bronze) * 1;
        return [
            'id' => (string)$this->id,
            'documentId' => (string)("$dateNow-$competition->id/$this->id"),
            'name' => $this->name,
            'image' =>  $path,
            'competitionName' => $competition->name,
            'totalRegistrationNo' => $reg_compatitors->count(),
            'competitorsCount' => $reg_compatitors->groupBy('compatitor_id')->count(),
            'singleRegistrationNo' => $registration_single->count(),
            'teamRegistrationNo' => $registration_team->count(),
            'totalPrice' => $totalPrice,
            'gold' => $singleRegistrations->where('position', 3)->count() + $teamRegistrations->where('position', 3)->countBy('team_id')->count(), 
            'silver' => $singleRegistrations->where('position', 2)->count() + $teamRegistrations->where('position', 2)->countBy('team_id')->count(),
            'bronze' => $singleRegistrations->where('position', 1)->count() + $teamRegistrations->where('position', 1)->countBy('team_id')->count(),
            'points' => $singleRegistrations->sum('position') + $pointsTeam,
            'teamsList' => $request->has('embed') && str_contains($request->embed, 'teamsList') ? TeamsRegistrationsResource::collection($teamCollection->get()) : 'embedable',
            'singlesList' => $request->has('embed') && str_contains($request->embed, 'singlesList') ? RegistratedCompatitorsSingleResource::collection($registration_single) : 'embedable',
            
        ];
    }
}
