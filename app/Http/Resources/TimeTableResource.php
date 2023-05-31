<?php

namespace App\Http\Resources;

use App\Models\Category;
use App\Models\Compatition;
use App\Models\Pool;
use App\Models\PoolTeam;
use App\Traits\LenghtOfCategory;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeTableResource extends JsonResource
{
    use LenghtOfCategory;
  
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $category = Category::where('id', $this->category_id)->first();
        $competiton = Compatition::where('id', $this->compatition_id)->first();
        $kata_or_kumite = $category->kata_or_kumite ? 'Kate' : 'Kumite';
        $gender = $category->gender == 1 ? 'M' : ($category->gender == 2 ? 'Ž' : 'M + Ž');
        $ekipno = $category->solo_or_team ? null : ' | Ekipno';
        $pool = $category->solo_or_team == 1 ? Pool::where('compatition_id', $this->compatition_id)->where('category_id', $this->category_id)->get() : PoolTeam::where('compatition_id', $this->compatition_id)->where('category_id', $this->category_id)->get();
        $pools = PoolResource::collection($pool);
        $poolsTeam = PoolsTeamResource::collection($pool);
        $groupsTotal = $this->categoryDuration($competiton, $category);
        
        $data = $ekipno == null  ? $pools->whereIn('pool_type', ['G', 'SF', 'FM']) : $poolsTeam->whereIn('pool_type', ['G', 'SF', 'FM']);
        $repesaz = $ekipno == null  ? $pools->whereIn('pool_type', ['R', 'RSF', 'RFM']) : $poolsTeam->whereIn('pool_type', ['R', 'RSF', 'RFM']);

        $delay = 0;
        $etoStart = 0;
        if($this->finish_time == null && $this->started_time != null) {
            $etoStart = strtotime($this->eto_start);
            $startedAt = strtotime($this->started_time);
            $delay = ($startedAt - $etoStart)/60;
        }
        if($this->finish_time != null) {
            $etoFinish = strtotime($this->eto_finish);
            $finishTime = strtotime($this->finish_time);
            $delay = ($finishTime - $etoFinish)/60;
        }

        return [
            'id' => $this->id,
            'tatami' => 'Tatami ' . $this->tatami_no,
            'tatamiNo' => $this->tatami_no,
            'category' => [
                'id' => $category->id,
                'name' => $kata_or_kumite . ' | ' . $gender . ' | ' . $category->name . ' ' . $category->category_name  . $ekipno,
                'isTeam' => (boolean)!$category->solo_or_team,
                'haveRematch' => (boolean)$category->repesaz,
            ],              
            'etoStart' => date('H:i', strtotime($this->eto_start)),
            'etoFinish' => date('H:i', strtotime($this->eto_finish)),
            'delay' => -$delay,
            'startedAt' => $this->started_time != null ? date('H:i', strtotime($this->started_time)) : null,
            'finishedAt' => $this->finish_time != null ? date('H:i', strtotime($this->finish_time)) : null,
            'status' => $this->status,
            'roundsTotal' => $groupsTotal['categoryPoolsFront'],
            'groups' => $request->has('embed') && str_contains($request->embed, 'groups') ? $data->toArray() : 'embbedable',
            'rematch' => $request->has('embed') && str_contains($request->embed, 'rematch') ? $repesaz->toArray() : 'embbedable'

        ];
    }
}
