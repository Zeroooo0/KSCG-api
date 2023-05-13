<?php

namespace App\Http\Resources;

use App\Models\Roles;
use App\Models\SpecialPersonal;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;

class SpecPersonRoleResource extends JsonResource
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
        $role = 'Uprava';
        if($this->role == 1) {
            $role = 'Sudija';
      
        }
        if($this->role == 2) {
            $role = 'Trener';
        }
        
        return [
            'id' => (string)$this->id,
            'specialPersonalId' => (string)$this->id,
            'name' => $this->name . ' ' . $this->last_name,
            'title' => 'Sudija',
            'role' => 'Sudija',
            'role' => $role,
            'status' => (boolean)$this->status,
            'image' => $path,
            "clubName" => null,
            "competitionName" => null,

        ];
    }
}
