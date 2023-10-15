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
        
        $allPoints = $this->registration_id !== null ? 
            KataPointPanel::where('pool_id', $this->pool_id)->where('registration_id', $this->registration_id)->orderBy('points', 'desc')->get():
            KataPointPanel::where('pool_id', $this->pool_id)->where('team_id', $this->team_id)->orderBy('points', 'desc')->get();
        $validationCount = $allPoints->first()->id == $this->id || $allPoints->last()->id == $this->id ;

        return [
            'id' => (string)$this->id,
            'judge' => "Sudija $this->judge",
            'points' => $this->points,
            'dosentCount' => $validationCount
        ];
    }
}
