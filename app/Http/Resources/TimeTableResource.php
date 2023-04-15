<?php

namespace App\Http\Resources;

use App\Models\Category;
use App\Models\Compatition;
use App\Models\Pool;
use App\Traits\LenghtOfCategory;
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
        $competition = Compatition::where('id', $this->compatition_id)->first();
        $category = Category::where('id', $this->category_id)->first();
        $kata_or_kumite = $category->kata_or_kumite ? 'Kate' : 'Kumite';
        $gender = $category->gender == 1 ? 'M' : ($category->gender == 2 ? 'Ž' : 'M + Ž');
        $ekipno = $category->solo_or_team  ? null : ' | Ekipno';
        
        return [
            'id' => $this->id,
            'tatami' => 'Tatami ' . $this->tatami_no,
            'category' => [
                'id' => $category->id,
                'name' => $kata_or_kumite . ' | ' . $gender . ' | ' . $category->name . ' ' . $category->category_name  . $ekipno
            ],
            'competition' => [
                'id' => $competition->id,
                'name' => $competition->name,
            ],
            
            'etoStart' => date('H:m', strtotime($this->eto_start)),
            'etoFinish' => date('H:m', strtotime($this->eto_finish)),
            'startedAt' => $this->started_time != null ? date('H:m', strtotime($this->started_time)) : null,
            'finishedAt' => $this->finish_time != null ? date('H:m', strtotime($this->finish_time)) : null,
            'status' => $this->status,
            'matches' => 'testing'
      

        ];
    }
}
