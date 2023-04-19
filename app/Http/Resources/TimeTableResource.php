<?php

namespace App\Http\Resources;

use App\Models\Category;
use App\Models\Pool;
use App\Models\PoolTeam;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeTableResource extends JsonResource
{
  
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        
        $category = Category::where('id', $this->category_id)->first();
        $kata_or_kumite = $category->kata_or_kumite ? 'Kate' : 'Kumite';
        $gender = $category->gender == 1 ? 'M' : ($category->gender == 2 ? 'Ž' : 'M + Ž');
        $ekipno = $category->solo_or_team  ? null : ' | Ekipno';
        $pool = $category->solo_or_team == 1 ? Pool::where('compatition_id', $this->compatition_id)->where('category_id', $this->category_id)->get() : PoolTeam::where('compatition_id', $this->compatition_id)->where('category_id', $this->category_id)->get();
        $pools = PoolResource::collection($pool);
        $poolsTeam = PoolsTeamResource::collection($pool);
        $data = 'embeddable';
        if(str_contains($request->embed, 'groups')) {
            $data = $ekipno == null  ? $pools : $poolsTeam;
        }

        return [
            'id' => $this->id,
            'tatami' => 'Tatami ' . $this->tatami_no,
            'category' => [
                'id' => $category->id,
                'name' => $kata_or_kumite . ' | ' . $gender . ' | ' . $category->name . ' ' . $category->category_name  . $ekipno,   
            ],              
            'etoStart' => date('H:m', strtotime($this->eto_start)),
            'etoFinish' => date('H:m', strtotime($this->eto_finish)),
            'startedAt' => $this->started_time != null ? date('H:m', strtotime($this->started_time)) : null,
            'finishedAt' => $this->finish_time != null ? date('H:m', strtotime($this->finish_time)) : null,
            'status' => $this->status,
            'groups' => $data  

        ];
    }
}
