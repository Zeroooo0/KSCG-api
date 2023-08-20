<?php

namespace App\Http\Resources;

use App\Models\CompatitionClubsResults;
use App\Models\Registration;
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
        
        if($this->club->image != null) {
            $path =  $storage_url . $this->club->image->url;
        } else{
            $path = $storage_url . 'default/default-club.jpg';
        }

        $reg_compatitors = Registration::where('compatition_id', $this->compatition->id)->where('club_id', $this->club->id)->get();

        $registration_single = $reg_compatitors->where('team_or_single', 1)->sortBy('compatitor_id');
        $registration_team = $reg_compatitors->where('team_or_single', 0)->groupBy('team_id');

        $teams = [];
        foreach($registration_team as $key=>$val){
            $teams[] = $key;
        }
        $teamCollection = Team::whereIn('id',$teams);
        if(Auth::user() == null){
            $totalPrice = null;
        } else {
            $totalPrice = $this->total_price;
        }

        $dateNow = date('d/m/Y',strtotime($this->compatition->start_time_date));
        
        $compatitionId = $this->compatition->id;
        $clubID = $this->club->id;

        return [
            'id' => (string)$this->id,
            'documentId' => (string)("$dateNow-$compatitionId/$clubID"),
            'clubId' => $this->club->id,
            'name' => $this->club->name,
            'image' =>  $path,
            'compatitionName' => $this->compatition->name,
            'totalRegistrationNo' => $reg_compatitors->count(),
            'competitorsCount' => $this->no_compatitors,
            'singleRegistrationNo' => $this->no_singles,
            'teamRegistrationNo' => $this->no_teams == null ? 0 : $this->no_teams,
            'totalPrice' => $totalPrice,
            'gold' => $this->gold_medals, 
            'silver' => $this->silver_medals,
            'bronze' => $this->bronze_medals,
            'points' => $this->points,
            'teamsList' => $request->has('embed') && str_contains($request->embed, 'teamsList') ? TeamsRegistrationsResource::collection($teamCollection->get()) : 'embedable',
            'singlesList' => $request->has('embed') && str_contains($request->embed, 'singlesList') ? RegistratedCompatitorsSingleResource::collection($registration_single) : 'embedable',
            'totalGolds' => CompatitionClubsResults::where('compatition_id', $this->compatition->id)->sum('gold_medals')
        ];
    }
}
