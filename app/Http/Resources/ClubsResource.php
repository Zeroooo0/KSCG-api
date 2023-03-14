<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ClubsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $pib = Auth::user() != null ? $this->pib : 'Protected';
        $storage_url = env('APP_URL') . 'api/file/';
        
        if($this->image != null) {
            $path =  $storage_url . $this->image->url;
        } else{
            $path = $storage_url . 'default/default-club.jpg';
        }
        if($this->user_id == null) {
            $user_info = 'Connect to user!';
        } else{
            $user_info = [
                'id' => (string)$this->user->id,
                'name' => $this->user->name,
                'lastName' => $this->user->last_name,
                'email' => $this->user->email,
                'status' => (boolean)$this->user->status,
            ];
        }        
        
        return [
            'id' => (string)$this->id,
            'attributes' => [
                'name' => $this->name,
                'shortName' => $this->short_name,
                'pib' => $pib,
                'email' => $this->email,
                'phone' => $this->phone_number,
                'image' =>  $path 
            ],
            'location' => [
                'country' => $this->country,
                'city' => $this->town,
                'address' => $this->address,
            ],
            'user' => $user_info,
            'administrationCount' => count($this->roles),
            'competitorsCount' => count($this->compatitors),
        ];
    }
}
