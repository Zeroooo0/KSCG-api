<?php

namespace App\Http\Resources;

use App\Models\Club;
use App\Models\Compatition;
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
        $registration = $this->club_id;
        $arr = [];
        $storage_url = env('APP_URL') . 'api/file/';
        foreach ($this->categories as $category) {
            $categoryList = $category->pivot->category_id; 
            $arr[] = (string)$categoryList;
        }
        $clubs = [];
        foreach ($this->registrations->countBy('club_id') as $club=>$val) {   
            $clubs[] = $club;            
        }
        $imageUrl = null;
        if($this->image != null) {
            $imageUrl = $storage_url . $this->image->url;
        }
        
        return [
            'id' => (string)$this->id,
            'name' => $this->name,
            'hostName' => $this->host_name,
            'priceSingle' => $this->price_single,
            'priceTeam' => $this->price_team,
            'startTimeDate' => date($this->start_time_date),
            'registrationDeadline' => date($this->registration_deadline),
            'posterImage' => $imageUrl,
            'location' => [
                'country' => $this->country,
                'city' => $this->city,
                'address' => $this->address,
            ],
            'status' => (boolean)$this->status,
            'registrationStatus' => (boolean)$this->registration_status,
            'categories' => $arr,  //CategoriesResource::collection($this->categories)
            'registrations' => [
                'clubs' => $this->registrations->countBy('club_id')->count(),
                'compatitor' => $this->registrations->countBy('compatitor_id')->count(),
                'categories' => $this->registrations->countBy('category_id')->count(),
                'total' => $this->registrations->count(),
                'countries' => Club::whereIn('id', $clubs)->get()->countBy('country')->count(),
                'clubsData' => ClubsOnCompatitionResource::collection(Club::whereIn('id', $clubs)->get())
            ]

           
        ];
    }
}
