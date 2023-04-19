<?php

namespace App\Http\Resources;

use App\Models\Category;
use App\Models\Club;
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
                'resultText' => $isWinnerOne != null ? ($isWinnerOne ? 'Pobjeda' : 'Poraz') : null
            ];
            $two = [
                'id' => $teamTwo != null ? $teamTwo->id : null,
                'name' => $teamTwo != null ? $teamTwo->name . " ($cloubTwoShortName)" : null,
                'isWinner' => $isWinnerTwo,
                'resultText' => $isWinnerTwo != null  ? ($isWinnerTwo ? 'Pobjeda' : 'Poraz') : null 
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
        $nextMatchId = $this->pool_type != 'FM' ? $pools->where('pool', $nextPool)->where('group', $nextMatchGroup)->first()->id : null;

 
        return [
            'id' => (string)$this->id,
            'name' => $name,
            'group' => $this->pool,
            'match' => $this->group,
            'nextMatchId' => $nextMatchId,
            'startTime' => $this->start_time,
            'teamOne' => $one,
            'teamTwo' => $two
        ];
    }
}
