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
        $categories = env('APP_URL') . 'api/v1/compatition-categories/' . $this->id;
        $storage_url = env('APP_URL') . 'api/file/';

        $clubs = [];
        foreach ($this->registrations->countBy('club_id') as $club=>$val) {   
            $clubs[] = $club;            
        }
        $imageUrl = null;
        if($this->image != null) {
            $imageUrl = $storage_url . $this->image->url;
        }
        $documents = 'embeddable';
        $clubsData = 'embeddable';
        if(str_contains($request->embed, 'documents')) {
            if($this->document->first() != null) {
                $documents = DocumentsResource::collection($this->document);
            } else {
                $documents =  'Nema dokumenta';
            }
        }
        if(str_contains($request->embed, 'clubsData')) {
            if($this->document->first() != null) {
                $clubsData = ClubsOnCompatitionResource::collection(Club::whereIn('id', $clubs)->get());
            } else {
                $clubsData =  'Trenutno nema prijavljenih!';
            }
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
            'documents' => $documents,
            'status' => (boolean)$this->status,
            'registrationStatus' => (boolean)$this->registration_status,
            'categories' => $categories,  
            'registrations' => [
                'clubs' => $this->registrations->countBy('club_id')->count(),
                'compatitor' => $this->registrations->countBy('compatitor_id')->count(),
                'categories' => $this->registrations->countBy('category_id')->count(),
                'total' => $this->registrations->count(),
                'countries' => Club::whereIn('id', $clubs)->get()->countBy('country')->count(),
                'clubsData' => $clubsData
            ],
            

           
        ];
    }
}
