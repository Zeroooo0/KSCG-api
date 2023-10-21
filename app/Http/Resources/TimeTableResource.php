<?php

namespace App\Http\Resources;

use App\Models\Category;
use App\Models\Compatition;
use App\Models\Pool;
use App\Models\PoolTeam;
use App\Models\Registration;
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
        $registrationsPositions = RegistrationsResource::collection(Registration::where('compatition_id', $this->compatition_id)->where('category_id', $this->category_id)->where('position', '!=', null)->orderBy('position', 'desc')->get());
        $data = $ekipno == null  ? $pools->whereIn('pool_type', ['G', 'SF', 'FM', ]) : $poolsTeam->whereIn('pool_type', ['G', 'SF', 'FM']);
        $repesazOne = $ekipno == null  ? $pools->where('group', '1')->whereIn('pool_type', ['RE', 'REFM']) : $poolsTeam->where('group', '1')->whereIn('pool_type', ['RE', 'REFM']);
        $repesazTwo = $ekipno == null  ? $pools->where('group', '2')->whereIn('pool_type', ['RE', 'REFM']) : $poolsTeam->where('group', '2')->whereIn('pool_type', ['RE', 'REFM']);
        $roundRobin = $ekipno == null ? $pools->whereIn('pool_type', ['RR', 'RRSF', 'RRFM']) : $poolsTeam->whereIn('pool_type', ['RR', 'RRFM']);
        $kataRepesaz = $ekipno == null ? $pools->whereIn('pool_type', ['KRG3', 'KRG4', 'KRG10', 'KRG24', 'KRG48', 'KRG96', 'KRG192', 'KRGA', 'KRFM', 'KRSF'])->sortByDesc('points_reg_one')->sortBy('group')->sortBy('pool') : $poolsTeam->whereIn('pool_type', ['KRG3', 'KRG4', 'KRG10', 'KRG24', 'KRG48', 'KRG96', 'KRG192', 'KRGA', 'KRFM', 'KRSF'])->sortByDesc('points_team_one')->sortBy('group')->sortBy('pool');
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
        $roundsTotal = 0;
        if($competiton->rematch == 0) {
            $roundsTotal = $pool->where('pool_type', 'FM')->count() != 0 ? $pool->where('pool_type', 'FM')->first()->pool : 0;
        }
        if($competiton->rematch == 1 && $category->repesaz == 1 && $category->kata_or_kumite == 0) {
            $roundsTotal = 1;
            if($pool->where('pool_type', 'FM')->count() != 0) {
                $roundsTotal = $pool->where('pool_type', 'FM')->first()->pool;
            }
        }
        if($competiton->rematch == 1 && $category->repesaz == 0 && $category->kata_or_kumite == 0) {
            $roundsTotal = 1;
            if($pool->where('pool_type', 'FM')->count() != 0) {
                $roundsTotal = $pool->where('pool_type', 'FM')->first()->pool;
            }
        }
        if($competiton->rematch == 1 && $category->repesaz == 0 && $category->kata_or_kumite == 1) {
            $roundsTotal = 1;
            if($pool->where('pool_type', 'FM')->count() != 0) {
                $roundsTotal = $pool->where('pool_type', 'FM')->first()->pool;
            }
        }
        if($competiton->rematch == 1 && $category->repesaz == 1 && $category->kata_or_kumite == 1) {
            $arrOfPoolType = ['KRG3','KRFM'];
            $roundsTotal = $pool->whereIn('pool_type', $arrOfPoolType)->count() != 0 ? $pool->whereIn('pool_type', $arrOfPoolType)->first()->pool : 0;
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
            'competitionId' => $this->compatition->id,
            'etoStart' => date('H:i', strtotime($this->eto_start)),
            'etoFinish' => date('H:i', strtotime($this->eto_finish)),
            'delay' => -(int)$delay,
            'startedAt' => $this->started_time != null ? date('H:i', strtotime($this->started_time)) : null,
            'finishedAt' => $this->finish_time != null ? date('H:i', strtotime($this->finish_time)) : null,
            'status' => $this->status,
            'roundsTotal' => (string)$roundsTotal,
            'results' => $registrationsPositions,
            'groups' => $request->has('embed') && str_contains($request->embed, 'groups') ? $data->toArray() : 'embbedable',
            'rematchOne' => $request->has('embed') && str_contains($request->embed, 'rematch') ? $repesazOne->toArray() : 'embbedable',
            'rematchTwo' => $request->has('embed') && str_contains($request->embed, 'rematch') ? $repesazTwo->toArray() : 'embbedable',
            'roundRobin' => $request->has('embed') && str_contains($request->embed, 'roundRobin') ? $roundRobin->toArray() : 'embbedable',
            'kataRepesaz' => $request->has('embed') && str_contains($request->embed, 'kataRepesaz') ? $kataRepesaz->toArray() : 'embbedable'

        ];
    }
}
