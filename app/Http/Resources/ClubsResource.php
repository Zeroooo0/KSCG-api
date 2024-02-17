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

        if ($this->image != null) {
            $path =  $storage_url . $this->image->url;
        } else {
            $path = $storage_url . 'default/default-club.jpg';
        }
        if ($this->user == null) {
            $user_info = 'Connect to user!';
        } else {
            $user_info = [
                'id' => (string)$this->user->id,
                'name' => $this->user->name,
                'lastName' => $this->user->last_name,
                'email' => $this->user->email,
                'status' => (bool)$this->user->status,
            ];
        }

        $thisYear = date('Y-m-d', strtotime('first day of january this year'));
        $dataTeamGold = $this->registrations->where('updated_at', '>=', $thisYear)->where('team_or_single', 0)->where('position', 3)->countBy('team_id')->count();
        $dataTeamSilver = $this->registrations->where('updated_at', '>=', $thisYear)->where('team_or_single', 0)->where('position', 2)->countBy('team_id')->count();
        $dataTeamBronze = $this->registrations->where('updated_at', '>=', $thisYear)->where('team_or_single', 0)->where('position', 1)->countBy('team_id')->count();

        $dataSingleGold = $this->registrations->where('updated_at', '>=', $thisYear)->where('team_or_single', 1)->where('position', 3)->count();
        $dataSingleSilver = $this->registrations->where('updated_at', '>=', $thisYear)->where('team_or_single', 1)->where('position', 2)->count();
        $dataSingleBronze = $this->registrations->where('updated_at', '>=', $thisYear)->where('team_or_single', 1)->where('position', 1)->count();
        $haseComponents = str_contains($request->embed, 'components');

        $rolesAdministration = RolesResource::collection($this->roles->where('role', 0));
        $rolesCoach = RolesResource::collection($this->roles->where('role', 2));
        $competitors = ClubsCompatiorsResource::collection(Compatitor::where('club_id', $this->id)->where('status', 1)->get());

        $rolesArray = [];
        if (!$rolesAdministration->isEmpty()) {
            $rolesArray[] = [
                'title' => 'Uprava kluba',
                'roles' => $rolesAdministration
            ];
        }
        if (!$rolesCoach->isEmpty()) {
            $rolesArray[] = [
                'title' => 'Treneri',
                'roles' => $rolesCoach
            ];
        }
        if (!$competitors->isEmpty()) {
            $rolesArray[] = [
                'title' => 'Takmičari',
                'roles' => $competitors
            ];
        }
        $resultsType = 'ebeddable';
        if (str_contains($request->embed, 'resultsType')) {
            $wkf = $this->compatitionClubsResults->where('compatition_type', 'WKF');
            $ekf = $this->compatitionClubsResults->where('compatition_type', 'EKF');
            $bkf = $this->compatitionClubsResults->where('compatition_type', 'BKF');
            $mkf = $this->compatitionClubsResults->where('compatition_type', 'MKF');
            $ssekf = $this->compatitionClubsResults->where('compatition_type', 'SSEKF');
            $kscg = $this->compatitionClubsResults->where('compatition_type', 'KSCG');
            $turnaments = $this->compatitionClubsResults->where('compatition_type', 'Turniri');
            $resultsType = [
                [
                    'type' => 'gold',
                    'wkf' => (string)$wkf->sum('gold_medals'),
                    'ekf' => (string)$ekf->sum('gold_medals'),
                    'bkf' => (string)$bkf->sum('gold_medals'),
                    'mkf' => (string)$mkf->sum('gold_medals'),
                    'ssekf' => (string)$ssekf->sum('gold_medals'),
                    'kscg' => (string)$kscg->sum('gold_medals'),
                    'turnaments' => (string)$turnaments->sum('gold_medals'),

                ],
                [
                    'type' => 'silver',
                    'wkf' => (string)$wkf->sum('silver_medals'),
                    'ekf' => (string)$ekf->sum('silver_medals'),
                    'bkf' => (string)$bkf->sum('silver_medals'),
                    'mkf' => (string)$mkf->sum('silver_medals'),
                    'ssekf' => (string)$ssekf->sum('silver_medals'),
                    'kscg' => (string)$kscg->sum('silver_medals'),
                    'turnaments' => (string)$turnaments->sum('silver_medals'),


                ],
                [
                    'type' => 'bronze',
                    'wkf' => (string)$wkf->sum('bronze_medals'),
                    'ekf' => (string)$ekf->sum('bronze_medals'),
                    'bkf' => (string)$bkf->sum('bronze_medals'),
                    'mkf' => (string)$mkf->sum('bronze_medals'),
                    'ssekf' => (string)$ssekf->sum('bronze_medals'),
                    'kscg' => (string)$kscg->sum('bronze_medals'),
                    'turnaments' => (string)$turnaments->sum('bronze_medals'),
                ],

            ];
        }


        return [
            'id' => (string)$this->id,
            'name' => $this->name,
            'shortName' => $this->short_name,
            'status' => (bool)$this->status,
            'pib' => $pib,
            'email' => $this->email,
            'phone' => $this->phone_number,
            'image' =>  $path,
            'country' => $this->country,
            'city' => $this->town,
            'address' => $this->address,
            'user' => $user_info,
            'administrationCount' => count($this->roles),
            'competitorsCount' => count($this->compatitors->where('status', 1)),
            'gold' => $dataTeamGold + $dataSingleGold,
            'silver' => $dataTeamSilver + $dataSingleSilver,
            'bronze' => $dataTeamBronze + $dataSingleBronze,
            'components' => !$haseComponents ? "ebeddable" : $rolesArray,
            'resultsType' => $resultsType
        ];
    }
}
