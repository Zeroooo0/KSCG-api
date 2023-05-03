<?php

namespace App\Http\Resources;

use App\Models\Belt;
use App\Models\Registration;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class CompatitorsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $storage_url = env('APP_URL') . 'api/file/';
        $jmbg = Auth::user() != null ? (string)$this->jmbg : 'Protected';
        
        if($this->image != null) {
            $path =  $storage_url . $this->image->url;
        } else {
            if($this->gender == 1) {
                $path = $storage_url . 'default/default-m-user.jpg';
            } else{
                $path = $storage_url . 'default/default-f-user.jpg';
            }
        }
        $documents = 'embeddable';
        $results = 'embeddable';

        if(str_contains($request->embed, 'documents')) {
            if($this->document->first() != null) {
                $documents = DocumentsResource::collection($this->document);
            } else {
                $documents =  'Nema dokumenta';
            }
        }
        if(str_contains($request->embed, 'results')) {
            if($this->registrations->first() != null) {
                $results = ResultsResource::collection($this->registrations->sortByDesc('id'));
            } else {
                $results =  'Ne postoje prijave!';
            }
        }
        $registrations = Registration::where('compatitor_id', $this->id)->get();
        $gold = $registrations->where('position', 3)->count();
        $silver = $registrations->where('position', 2)->count();
        $bronze = $registrations->where('position', 1)->count();

        return [
            'id' => (string)$this->id,
            'kscgId' => $this->kscg_compatitor_id,
            'name' => $this->name,
            'lastName' => $this->last_name,
            'gender' => $this->gender,
            'jmbg' => $jmbg,
            'country' => $this->country,
            'weight' => $this->weight,
            'birthDay' => date($this->date_of_birth),
            'status' => (boolean)$this->status,
            'belt' => new BeltResource($this->belt),
            'image' => $path,
            'createAt' => date($this->created_at),
            'updatedAt' => date($this->updated_at),
            'club' => $this->club != null ??[
                'id' => (string)$this->club->id,
                'name' => $this->club->name,
                'shortName' => $this->club->short_name,
            ],
            'medals' => [
                'gold' => $gold,
                'silver' => $silver,
                'bronze' => $bronze,
            ],
            'documents' => $documents,
            'results' => $results
            
        ];
    }
}
