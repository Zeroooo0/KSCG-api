<?php

namespace App\Http\Resources;

use App\Models\KataPointPanel;
use Illuminate\Http\Resources\Json\JsonResource;

class KataPointPanelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $validationCount = false;
        $allPoints = KataPointPanel::where('pool_id', $this->pool_id)->where('registration_id', $this->registration_id)->get();

        if($allPoints->where('points', '<', $this->points)->count() == 0) {

            if($allPoints->where('points', '=', $this->points)->where('judge', '<', $this->judge)->count() == 0){
                $validationCount = true;
            }
        }
        if($allPoints->where('points', '>', $this->points)->count() == 0) {
            if($allPoints->where('points', '=', $this->points)->where('judge', '>', $this->judge)->count() == 0){
                $validationCount = true;
            }
        }
        return [
            'id' => (string)$this->id,
            'judge' => "Sudija $this->judge",
            'points' => $this->points,
            'dosentCount' => $validationCount
        ];
    }
}
