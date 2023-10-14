<?php

namespace App\Http\Resources;

use App\Models\Category;
use App\Models\Club;
use App\Models\KataPointPanel;
use App\Models\OfficialKata;
use App\Models\PoolTeam;
use App\Models\Registration;
use App\Models\Team;
use Illuminate\Http\Resources\Json\JsonResource;

class PoolsTeamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $category = $this->category;

        if($category->solo_or_team != 1) {
            $teamOne = $this->team_one != null ? Team::where('id', $this->team_one)->first() : null;
            $teamTwo = $this->team_two != null ? Team::where('id', $this->team_two)->first() : null;
            $teamOneClubId = $this->team_one != null ? Team::where('id', $this->team_one)->first()->registrations->first()->club_id : null;
            $teamTwoClubId = $this->team_two != null ? Team::where('id', $this->team_two)->first()->registrations->first()->club_id : null;
            $cloubOneShortName = $teamOneClubId != null ? Club::where('id', $teamOneClubId)->first()->short_name: null;
            $cloubTwoShortName = $teamTwoClubId != null ? Club::where('id', $teamTwoClubId)->first()->short_name: null;
            $isWinnerOne = $teamOne != null ? ( $this->winner_id == null ? null : ($this->team_one == $this->winner_id ? true : false )) : null;
            $isWinnerTwo = $teamTwo != null ? ( $this->winner_id == null ? null : ($this->team_two == $this->winner_id ? true : false )) : null;
            $one = [
                'id' => $teamOne != null ? $teamOne->id : null,
                'name' => $teamOne != null ? $teamOne->name . " ($cloubOneShortName)" : null,
                'isWinner' => $isWinnerOne,
                'resultText' => $isWinnerOne != null ? ($isWinnerOne ? 'Pobjeda' : 'Poraz') : null,
                'kataName' => $this->kata_one_id ? OfficialKata::where('id', $this->kata_one_id)->first()->name : null,
                'totalPoints' => $this->points_team_one,
                'allPoints' => KataPointPanelResource::collection(KataPointPanel::where('pool_team_id', $this->id)->where('team_id', $this->team_one)->orderBy('judge', 'asc')->get())
            
            ];
            $two = [
                'id' => $teamTwo != null ? $teamTwo->id : null,
                'name' => $teamTwo != null ? $teamTwo->name . " ($cloubTwoShortName)" : null,
                'isWinner' => $isWinnerTwo,
                'resultText' => $isWinnerTwo != null  ? ($isWinnerTwo ? 'Pobjeda' : 'Poraz') : null,
                'kataName' => $this->kata_two_id ? OfficialKata::where('id', $this->kata_two_id)->first()->name : null,
                'totalPoints' => $this->points_team_one,
                'allPoints' => KataPointPanelResource::collection(KataPointPanel::where('pool_team_id', $this->id)->where('team_id', $this->team_two)->orderBy('judge', 'asc')->get())
            
            ];

        }
        $name = null;

        if($this->pool_type == 'G'){
            $name = "Grupa";
        }
        if($this->pool_type == 'SF'){
            $name = "Polufinale";
        }
        if($this->pool_type == 'FM'){
            $name = "Finale";
        }
        $group = $this->group;
        $pools = PoolTeam::where('compatition_id', $this->compatition_id)->where('category_id', $this->category_id);

        

        $nextPool = $this->pool + 1;

        if($group % 2 == 0){
            $nextMatchGroup = $group / 2;
        }
        else{
            $nextMatchGroup = ($group + 1) / 2;
        }
        $nextMatchId = null;
        switch($this->pool_type) {
            case 'G':
                $name = 'Grupa';
                $nextMatchId = $pools->where('pool', $nextPool)->where('group', $nextMatchGroup)->first()->id;
                break;
            case 'SF':
                $name = 'Polufinale';
                $nextMatchId = $pools->where('pool', $nextPool)->where('group', $nextMatchGroup)->first()->id;
                break;
            case 'FM':
                $name = "Finale";
                $nextMatchId = null;
                break;
            case 'RE':
                $name = "Repesaž";
                $nextMatchId = $this->id + 1;
                break;
            case 'REFM':
                $name = "Repesaž Finale";
                $nextMatchId = null;
                break;
            case 'RR':
                $name = "Round Robin";
                $nextMatchId = $this->id + 1;
                break;
            case 'RRSF':
                $name = "Round Robin Polufinali";
                $nextMatchId = $this->id + 1;
                break;
            case 'RRFM':
                $name = "Round Robin Finale";
                $nextMatchId = null;
                break;
            case 'KRG3':
                $name = 'Kate finale';
                $nextMatchId = null;
                break;
            case 'KRG4':
                $name = 'Kate grupa';
                $nextMatchId = null;
                break;
            case 'KRG10':
                $name = 'Kate grupa';
                $nextMatchId = null;
                break;
            case 'KRG24':
                $name = 'Kate grupa';
                $nextMatchId = null;
                break;
            case 'KRG48':
                $name = 'Kate grupa';
                $nextMatchId = null;
                break;
            case 'KRG96':
                $name = 'Kate grupa';
                $nextMatchId = null;
                break;
            case 'KRG192':
                $name = 'Kate grupa';
                $nextMatchId = null;
                break;
            case 'KRGA':
                $name = 'Kate grupa';
                $nextMatchId = null;
                break;
            case 'KRSF':
                $name = 'Kate bronza';
                $nextMatchId = null;
                break;
            case 'KRFM':
                $name = 'Kate zlato';
                $nextMatchId = null;
                break;
        }

 
        return [
            'id' => (string)$this->id,
            'name' => $name,
            'group' => (string)$this->pool,
            'match' => (string)$this->group,
            'nextMatchId' => (string)$nextMatchId,
            'startTime' => $this->start_time,
            'competitorOne' => $one,
            'competitorTwo' => $two
        ];
    }
}
