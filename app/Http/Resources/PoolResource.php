<?php

namespace App\Http\Resources;

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
            $isWinnerOne = $compatitorOne != null ? ($compatitorOne->id == $this->winner_id ? true : false ) : null;
            $isWinnerTwo = $compatitorTwo != null ? ($compatitorTwo->id == $this->winner_id ? true : false ) : null;
            $one = [
                'registrationId' => $this->registration_one,
                'name' => $compatitorOne != null ? "$compatitorOne->name $compatitorOne->last_name ($compatitorOneClub)" : null,
                'isWinner' => $isWinnerOne,
                'resultText' => $isWinnerOne != null ? ($isWinnerOne ? 'Pobjeda' : 'Poraz') : null ,
            ];
            $two = [
                'registrationId' => $this->registration_two,
                'name' => $compatitorTwo != null ? "$compatitorTwo->name $compatitorTwo->last_name ($compatitorTwoClub)" : null,
                'isWinner' => $isWinnerTwo,
                'resultText' => $isWinnerTwo != null  ? ($isWinnerTwo ? 'Pobjeda' : 'Poraz') : null ,
            ];
        } else{
            $one = null;
            $two = null;
        }
        $group = $this->group;
        $pools = Pool::where('compatition_id', $this->compatition_id)->where('category_id', $this->category_id);
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
            'competitorOne' => $one,
            'competitorTwo' => $two
        ];
    }
}
