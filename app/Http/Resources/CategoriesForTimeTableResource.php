<?php

namespace App\Http\Resources;

use App\Models\Category;
use App\Models\Compatition;
use App\Models\Pool;
use App\Models\PoolTeam;
use App\Models\TimeTable;
use App\Traits\LenghtOfCategory;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoriesForTimeTableResource extends JsonResource
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
        $competition = Compatition::where('id', $this->pivot->compatition_id)->first();
        $category = Category::where('id', $this->id)->first();

        $pool = $this->solo_or_team == 1 ? Pool::where('compatition_id', $this->pivot->compatition_id)->where('category_id', $this->id)->get() : PoolTeam::where('compatition_id', $this->pivot->compatition_id)->where('category_id', $this->id)->get();

        $catSpec = $this->categoryDuration($competition, $category);
        $kata_or_kumite = $this->kata_or_kumite ? 'Kate' : 'Kumite';
        $gender = $this->gender == 1 ? 'M' : ($this->gender == 2 ? 'Ž' : 'M + Ž');
        $ekipno = $this->solo_or_team  ? null : ' | Ekipno';

        return [
            'id' => $this->id,
            'kataOrKumite' => $kata_or_kumite,
            'isTeam' => !$this->solo_or_team,
            'combinedName' => $kata_or_kumite . ' | ' . $gender . ' | ' . $this->name . ' ' . $this->category_name  . $ekipno,
            'categoryDuration' => $catSpec['categoryDuration'],
            'categoryGroups' => $catSpec['categoryGroupsFront'],
            'categoryPools' => $catSpec['categoryPoolsFront'],
            'categoryRegistration' => $catSpec['categoryRegistrations'],

        ];
    }
}
