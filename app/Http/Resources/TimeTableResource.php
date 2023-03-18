<?php

namespace App\Http\Resources;

use App\Models\Category;
use App\Models\Compatition;
use App\Models\Pool;
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
        $competition = Compatition::where($this->comapatition_id)->first();
        $category = Category::where($this->category_id)->first();
        $kata_or_kumite = $category->kata_or_kumite ? 'Kate' : 'Kumite';
        $gender = $category->gender == 1 ? 'M' : ($category->gender == 2 ? 'Ž' : 'M + Ž');
        return [
            'id' => $this->id,
            'tatami' => 'Tatami ' . $this->tatami_no
            'category' => $gender

        ];
    }
}
