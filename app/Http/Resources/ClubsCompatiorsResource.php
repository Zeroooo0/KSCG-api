<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClubsCompatiorsResource extends JsonResource
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
        if($this->image != null) {
            $path =  $storage_url . $this->image->url;
        } else {
            if($this->gender == 1) {
                $path = $storage_url . 'default/default-m-user.jpg';
            } else{
                $path = $storage_url . 'default/default-f-user.jpg';
            }
        }
        return [
            'id' => (string)$this->id,
            'name' => $this->name . ' ' . $this->last_name,   
            'combinedName' => $this->name . ' ' . $this->last_name,   
            'kscgId' => $this->kscg_compatitor_id,
            'brthDay' => date($this->date_of_birth),      
            'belt' => $this->belt,
            'image' => $path
        ];
    }
}
