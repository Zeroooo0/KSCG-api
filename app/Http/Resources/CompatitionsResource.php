<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompatitionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        
        
        $arr = [];
        foreach ($this->categories as $category) {
            $categoryList = $category->pivot->category_id; 
            $arr[] = (string)$categoryList;
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'hostName' => $this->host_name,
            'priceSingle' => $this->price_single,
            'priceTeam' => $this->price_team,
            'startTimeDate' => $this->start_time_date,
            'registrationDeadline' => date($this->registration_deadline),
            'eventLocation' => [
                'country' => $this->country,
                'city' => $this->city,
                'address' => $this->address,
            ],
            'status' => (boolean)$this->status,
            'registrationStatus' => (boolean)$this->registration_status,
            'categories' => $arr  //CategoriesResource::collection($this->categories)
        ];
    }
}
