<?php

namespace App\Http\Resources;

use App\Models\Compatitor;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ClubsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $pib = Auth::user() != null ? $this->pib : 'Protected';
        $storage_url = env('APP_URL') . 'api/file/';
        
        if($this->image != null) {
            $path =  $storage_url . $this->image->url;
        } else{
            $path = $storage_url . 'default/default-club.jpg';
        }
        if($this->user == null) {
            $user_info = 'Connect to user!';
        } else{
            $user_info = [
                'id' => (string)$this->user->id,
                'name' => $this->user->name,
                'lastName' => $this->user->last_name,
                'email' => $this->user->email,
                'status' => (boolean)$this->user->status,
            ];
        }        


        $dataTeamGold = $this->registrations->where('team_or_single', 0)->where('position', 3)->countBy('team_id')->count();
        $dataTeamSilver = $this->registrations->where('team_or_single', 0)->where('position', 2)->countBy('team_id')->count();
        $dataTeamBronze = $this->registrations->where('team_or_single', 0)->where('position', 1)->countBy('team_id')->count();

        $dataSingleGold = $this->registrations->where('team_or_single', 1)->where('position', 3)->count();
        $dataSingleSilver = $this->registrations->where('team_or_single', 1)->where('position', 2)->count();
        $dataSingleBronze = $this->registrations->where('team_or_single', 1)->where('position', 1)->count();
        $haseComponents = str_contains($request->embed, 'components');

        
        $clubAdministration = [
            'title' => 'Uprava kluba',
            'roles' => RolesResource::collection($this->roles->where('role', 0))
        ];
        $clubCoachList = [
            'title' => 'Treneri',
            'roles' => RolesResource::collection($this->roles->where('role', 2))
        ];
        $clubCompetitors = [
            'title' => 'Takmičari',
            'roles' => ClubsCompatiorsResource::collection(Compatitor::where('club_id', $this->id)->paginate($request->perPage))
         
        ];


        return [
            'id' => (string)$this->id,
            'name' => $this->name,
            'shortName' => $this->short_name,
            'status' => (boolean)$this->status,
            'pib' => $pib,
            'email' => $this->email,
            'phone' => $this->phone_number,
            'image' =>  $path,
            'country' => $this->country,
            'city' => $this->town,
            'address' => $this->address,
            'user' => $user_info,
            'administrationCount' => count($this->roles),
            'competitorsCount' => count($this->compatitors),
            'gold' => $dataTeamGold + $dataSingleGold,
            'silver' => $dataTeamSilver + $dataSingleSilver,
            'bronze' => $dataTeamBronze + $dataSingleBronze,
            'components' => !$haseComponents ? "ebeddable" : [$clubAdministration, $clubCoachList, $clubCompetitors ]
        ];
    }
}
