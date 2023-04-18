<?php

namespace App\Http\Resources;

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
            $one = [
                'registrationId' => $this->registration_one,
                'name' => $compatitorOne != null ? "$compatitorOne->name $compatitorOne->last_name ($compatitorOneClub)" : null
            ];
            $two = [
                'registrationId' => $this->registration_two,
                'name' => $compatitorTwo != null ? "$compatitorTwo->name $compatitorTwo->last_name ($compatitorTwoClub)" : null
            ];
        } else{
            $one = null;
            $two = null;
        }

        return [
            'id' => (string)$this->id,
            'poolType' => $this->pool_type,
            'poolNo' => $this->pool,
            'groupNo' => $this->group,
            'winnerId' => $this->winner_id,
            'looserId' => $this->winner_id,
            'startTime' => $this->start_time,
            'competitorOne' => $one,
            'competitorTwo' => $two
        ];
    }
}
