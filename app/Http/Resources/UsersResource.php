<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UsersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $userType = 'Club Administrator';
        if($this->user_type == 1) {
            $userType = 'Commision Administrator';
        }
        if($this->user_type == 2) {
            $userType = 'Administrator';
        }
        
        if($this->user_type == 0 && $this->club != null) {
            $data = [
                'id' => (string)$this->club->id,
                'name' => $this->club->name,
                'shortName' => $this->club->short_name,
            ];
        } else {
            $data = 'Ne posjeduje Klub';
        }
        return [
            'id' => (string)$this->id,
            'user' => [
                'name' => $this->name,
                'lastName' => $this->last_name,
                'email' => $this->email
            ],
            'status' => (boolean)$this->status,
            'userType' =>  $userType,
            'club' => $data
        ];
    }
}
