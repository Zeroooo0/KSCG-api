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
        $registrations = $this->registrations;
        function teamCount($position, $registrations) 
        {
            return $registrations->where('team_or_single', 0)->where('position', $position)->countBy('team_id')->count();
        }
        function singleCount($position, $registrations) 
        {
            return $registrations->where('team_or_single', 1)->where('position', $position)->countBy('team_id')->count();
        }
        return [
            'id' => (string)$this->id,
            'name' => $this->name,
            'shortName' => $this->short_name,
            'pib' => $pib,
            'email' => $this->email,
            'phone' => $this->phone_number,
            'image' =>  $path,
            'country' => $this->country,
            'city' => $this->town,
            'address' => $this->address,
            'user' => $user_info,
            'administrationCount' => count($this->roles),
            'competitorsCount' => count($this->compatitors),
            'gold' => singleCount(3, $registrations) + teamCount(3, $registrations),
            'silver' => singleCount(2, $registrations) + teamCount(2, $registrations),
            'bronze' => singleCount(1, $registrations) + teamCount(1, $registrations),
        ];
    }
}
