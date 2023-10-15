<?php

namespace App\Http\Resources;

use App\Models\KataPointPanel;
use App\Models\OfficialKata;
use App\Models\Pool;
use App\Models\Registration;
use Illuminate\Http\Resources\Json\JsonResource;

class PoolResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $ekipno = $this->category->solo_or_team  ? null : ' | Ekipno';
        $compatitorOne = $this->registration_one != null ? Registration::where('id', $this->registration_one)->first()->compatitor : null;
        $compatitorTwo = $this->registration_two != null ? Registration::where('id', $this->registration_two)->first()->compatitor : null;
    
        $compatitorOneClub = $this->registration_one != null && Registration::where('id', $this->registration_one)->first()->club != null ? Registration::where('id', $this->registration_one)->first()->club->short_name : null;
        $compatitorTwoClub = $this->registration_two != null && Registration::where('id', $this->registration_two)->first()->club != null ? Registration::where('id', $this->registration_two)->first()->club->short_name : null;
        if($ekipno == null) {
            $isWinnerOne = $compatitorOne != null ? ($this->winner_id == null ? null : ($this->registration_one == $this->winner_id ? true : false )) : null;
            $isWinnerTwo = $compatitorTwo != null ? ($this->winner_id == null ? null : ($this->registration_two == $this->winner_id ? true : false )) : null;
            $one = [
                'registrationId' => $this->registration_one,
                'name' => $compatitorOne != null ? "$compatitorOne->name $compatitorOne->last_name ($compatitorOneClub)" : null,
                'isWinner' => $isWinnerOne,
                'resultText' => $isWinnerOne !== null ? ($isWinnerOne ? 'Pobjeda' : 'Poraz') : null ,
                'kataName' => $this->kata_one_id ? OfficialKata::where('id', $this->kata_one_id)->first()->name : null,
                'totalPoints' => $this->points_reg_one,
                'allPoints' => KataPointPanelResource::collection(KataPointPanel::where('pool_id', $this->id)->where('registration_id', $this->registration_one)->orderBy('judge', 'asc')->get())
            ];
            $two = [
                'registrationId' => $this->registration_two,
                'name' => $compatitorTwo != null ? "$compatitorTwo->name $compatitorTwo->last_name ($compatitorTwoClub)" : null,
                'isWinner' => $isWinnerTwo,
                'resultText' => $isWinnerTwo !== null  ? ($isWinnerTwo ? 'Pobjeda' : 'Poraz') : null ,
                'kataName' => $this->kata_two_id ? OfficialKata::where('id', $this->kata_two_id)->first()->name : null,
                'totalPoints' => $this->points_reg_two,
                'allPoints' => KataPointPanelResource::collection(KataPointPanel::where('pool_id', $this->id)->where('registration_id', $this->registration_two)->orderBy('judge', 'asc')->get())
            ];
        } else{
            $one = null;
            $two = null;
        }
        $group = $this->group;
        $pools = Pool::where('compatition_id', $this->compatition_id)->where('category_id', $this->category_id);
        
        
        $nextPool = $this->pool + 1;

        if($group % 2 == 0){
            $nextMatchGroup = $group / 2;
        }
        else{
            $nextMatchGroup = ($group + 1) / 2;
        }
        $nextGroup = $this->group + 1;
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
            'poolType' => $this->pool_type,
            'group' => (string)$this->pool,
            'match' => (string)$this->group,
            'nextMatchId' => (string)$nextMatchId,
            'startTime' => $this->start_time,
            'competitorOne' => $one,
            'competitorTwo' => $two
        ];
    }
}
