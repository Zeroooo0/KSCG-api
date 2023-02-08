<?php

namespace App\Http\Resources;

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
        $jmbg = Auth::user() !== null ? (string)$this->jmbg : 'Protected';
        
        if($this->image !== null) {
            $path =  $storage_url . $this->image->url;
        } else {
            if($this->gender == 'M') {
                $path = $storage_url . 'default/default-m-user.jpg';
            } else{
                $path = $storage_url . 'default/default-f-user.jpg';
            }
        }
        if($this->document->first() !== null) {
            $documents = DocumentsResource::collection($this->document);
        } else {
            $documents =  'Nema dokumenta';
        }
        
        return [
            'id' => (string)$this->id,
            'kscgId' => $this->kscg_compatitor_id,
            'compatitor' => [
                'name' => $this->name,
                'lastName' => $this->last_name,
                'jmbg' => $jmbg,
                'image' => $path,
                'createAt' => $this->created_at,
                'updatedAt' => $this->updated_at
            ],
            'validation' => [
                'status' => (boolean)$this->status,
                'gender' => $this->gender,
                'belt' => $this->belt,
                'brthDay' => $this->date_of_birth,
                'weight' => $this->weight
            ],
            'relationships' => [
                'id' => (string)$this->club->id,
                'clubName' => $this->club->name,
                'clubShortName' => $this->club->short_name,
            ],
            'documents' => $documents
        ];
    }
}
