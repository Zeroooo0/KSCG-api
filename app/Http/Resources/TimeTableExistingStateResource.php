<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TimeTableExistingStateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $tatanuNumber = $this->tatami_no;
        $timeTable = $this->timeTable;
        //return $tatanuNumber;
        $constructObj = [];
        for($i = 1; $i <= $tatanuNumber; $i++) {
            $singleTatami= $timeTable->where('tatami_no', $i)->sortBy('order_no');
            $tatamiObj = [];
           foreach($singleTatami as $tatami) {
                $tatamiObj[] = new CategoriesForTimeTableResource($this->categories->where('id', $tatami->category_id)->first());
           }
           $constructObj["tatami_$i"] = $tatamiObj;
        }
        return $constructObj;
    }
}
