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
        $data_name = 'userType';
        $data = $userType;
        if($this->user_type == 0 && $this->club != null) {
            $data_name = 'club';
            $data = [
                'id' => (string)$this->club->id,
                'name' => $this->club->name,
                'shortName' => $this->club->short_name,
            ];
        }
        return [
            'id' => (string)$this->id,
            'user' => [
                'name' => $this->name,
                'lastName' => $this->last_name,
                'email' => $this->email
            ],
            'status' => (boolean)$this->status,
            $data_name =>  $data

        ];
    }
}
