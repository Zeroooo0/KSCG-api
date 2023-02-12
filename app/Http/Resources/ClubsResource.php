<?php

namespace App\Http\Resources;

use App\Models\SpecialPersonal;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
        $pib = Auth::user() !== null ? $this->pib : 'Protected';
        $storage_url = env('APP_URL') . 'api/file/';
        
        if($this->image !== null) {
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

        if($request->embed !== null) {
            if(str_contains($request->embed, 'compatitors')) {
                $compatitors = ClubsCompatiorsResource::collection($this->compatitors);
            } else {
                $compatitors = count($this->compatitors);
            }
            if(str_contains($request->embed, 'administration')) {
                if($this->roles->first() !== null) {
                    $administration = ClubsAdministrationResource::collection($this->roles);
                } else {
                    $administration = count($this->roles);
                }
            } else {
                $administration = count($this->roles);
            }
        } else {
            $compatitors = count($this->compatitors);
            $administration = count($this->roles);
        }

        return [
            'id' => (string)$this->id,
            'attributes' => [
                'name' => $this->name,
                'shortName' => $this->short_name,
                'pib' => $pib ,
                'email' => $this->email,
                'phone' => $this->phone_number,
                'image' =>  $path 
            ],
            'location' => [
                'country' => $this->country,
                'city' => $this->town,
                'address' => $this->address,
            ],
            'userData' => $user_info,
            'clubAdministration' => $administration,
            'compatitorsInClub' => $compatitors
        ];
    }
}
