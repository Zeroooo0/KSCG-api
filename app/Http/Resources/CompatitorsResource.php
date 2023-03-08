<?php

namespace App\Http\Resources;

use App\Models\Belt;
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
        $updateImage = env('APP_URL') . 'api/v1/compatitor-image/' . $this->id;
        return [
            'id' => (string)$this->id,
            'kscgId' => $this->kscg_compatitor_id,
            'compatitor' => [
                'name' => $this->name,
                'lastName' => $this->last_name,
                'jmbg' => $jmbg,
                'image' => $path,
                'createAt' => date($this->created_at),
                'updatedAt' => date($this->updated_at)
            ],
            'validation' => [
                'status' => (boolean)$this->status,
                'gender' => $this->gender,
                'brthDay' => date($this->date_of_birth),
                'weight' => $this->weight,
                'belt' => new BeltResource($this->belt),
            ],
            'club' => [
                'id' => (string)$this->club->id,
                'name' => $this->club->name,
                'shortName' => $this->club->short_name,
            ],
            'documents' => $documents,
            'results' => $results
            
        ];
    }
}
