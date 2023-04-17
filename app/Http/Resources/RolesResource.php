<?php

namespace App\Http\Resources;

use App\Models\Roles;
use App\Models\SpecialPersonal;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;

class RolesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
    
        $specialPersonal = SpecialPersonal::where('id', $this->special_personals_id)->first();
        $storage_url = env('APP_URL') . 'api/file/';
        if($specialPersonal->image != null) {
            $path =  $storage_url . $specialPersonal->image->url;
        } else {
            if($specialPersonal->gender == 'M') {
                $path = $storage_url . 'default/default-m-user.jpg';
            } else{
                $path = $storage_url . 'default/default-f-user.jpg';
            }
        }
        $role = 'Uprava';
        $val = 'clubName';
        if($this->role == 1) {
            $role = 'Sudija';
            $val = 'competitionName';
        }
        if($this->role == 2) {
            $role = 'Trener';
        }
        
        return [
            'id' => (string)$this->id,
            'specialPersonalId' => (string)$specialPersonal->id,
            'name' => $specialPersonal->name . ' ' . $specialPersonal->last_name,
            'title' => $this->title,
            'role' => $role,
            'status' => (boolean)$specialPersonal->status,
            'image' => $path,
            $val => $this->roleable->name,
            'registeredOn' => date('Y-m-d H:m:s', strtotime($this->roleable->created_at))
        ];
    }
}
