<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SpecialPersonalsResource extends JsonResource
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
            'id' => $this->id,
            'basicInfo' => [
                'name' => $this->name,
                'lastName' => $this->last_name,
                'country' => $this->country,
                'email' => $this->email,
                'phone' => $this->phone_number,
                'rolle' => $this->rolle,
                'status' => (boolean)$this->status,
                'gender' => $this->gender,
                'image' => $path
            ],
            'documents' => $documents
        ];
    }
}
