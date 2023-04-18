<?php

namespace App\Http\Resources;

use App\Models\Category;
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
        $one = null;
        $two = null;
        if($category->solo_or_team != 1) {
            $one = $this->team_one != null ? new TeamResource(Team::where('id', $this->team_one)->first()) : null;
            $two = $this->team_two != null ? new TeamResource(Team::where('id', $this->team_two)->first()) : null;
        }

        return [
            'id' => (string)$this->id,
            'poolType' => $this->pool_type,
            'poolNo' => $this->pool,
            'groupNo' => $this->group,
            'winnerId' => $this->winner_id,
            'looserId' => $this->winner_id,
            'startTime' => $this->start_time,
            'teamOne' => $one,
            'teamTwo' => $two
        ];
    }
}
