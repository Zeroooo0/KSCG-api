<?php

namespace App\Http\Resources;

use App\Models\Registration;
use Illuminate\Http\Resources\Json\JsonResource;

class PoolResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $kata_or_kumite = $this->category->kata_or_kumite ? 'Kate' : 'Kumite';
        $gender = $this->category->gender == 1 ? 'M' : ($this->gender == 2 ? 'Ž' : 'M + Ž');
        $ekipno = $this->category->solo_or_team  ? null : ' | Ekipno';
        $compatitorOne = $this->registration_one != null ? Registration::where('id', $this->registration_one)->first()->compatitor : null;
        $compatitorTwo = $this->registration_two != null ? Registration::where('id', $this->registration_two)->first()->compatitor : null;
        $compatitorOneClub = $this->registration_one != null ? Registration::where('id', $this->registration_one)->first()->club->short_name : null;
        $compatitorTwoClub = $this->registration_two != null ? Registration::where('id', $this->registration_two)->first()->club->short_name : null;

        return [
            'id' => (string)$this->id,
            'compatitionName' => $this->compatition->name,
            'category' => $kata_or_kumite . ' | ' . $gender . ' | ' . $this->category->name . ' ' . $this->category->category_name  . $ekipno,
            'poolType' => $this->pool_type,
            'poolNo' => $this->pool,
            'groupNo' => $this->group,
            'status' => $this->status,
            'competitorOne' => $compatitorOne != null ? "$compatitorOne->name $compatitorOne->last_name ($compatitorOneClub)" : null,
            'competitorTwo' => $compatitorTwo != null ? "$compatitorTwo->name $compatitorTwo->last_name ($compatitorTwoClub)" : null
        ];
    }
}
