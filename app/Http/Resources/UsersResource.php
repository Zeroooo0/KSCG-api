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
        if($this->user_type == 0) {
            if($this->club!== null) {
                $data_name = 'clubData';
                $data = [
                    'id' => $this->club->id,
                    'name' => $this->club->name,
                    'shortName' => $this->club->short_name,
                ];
            } else {
                $data_name = 'clubData';
                $data = 'Nije odredjen Klub!';
            }
        } else {
            $data_name = 'adminType';
            $data = 'Admin ' . $this->user_type == 1 ? 'Global' : 'Comission';
        }


        return [
            'id' => (string)$this->id,
            'user' => [
                'name' => $this->name,
                'lastName' => $this->last_name,
                'email' => $this->email
            ],
            'userAbility' => [
                'userType' => $this->user_type,
                'status' => (boolean)$this->status,
            ],
            $data_name => $data,

        ];
    }
}
