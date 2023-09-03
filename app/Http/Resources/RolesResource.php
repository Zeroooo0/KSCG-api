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
            if($specialPersonal->gender == 1) {
                $path = $storage_url . 'default/default-m-user.jpg';
            } else{
                $path = $storage_url . 'default/default-f-user.jpg';
            }
        }
        $role = 'Uprava';
        $val = 'clubName';
        $val = 'competitionName';
        if($this->role == 1) {
            $role = 'Sudija';
      
        }
        if($this->role == 2) {
            $role = 'Trener';
        }
        $compatition = null;
        
        
        return [
            'id' => (string)$this->id,
            'specialPersonalId' => (string)$specialPersonal->id,
            'combinedName' => $specialPersonal->name . ' ' . $specialPersonal->last_name,
            'email' => $specialPersonal->email,
            'phone' => $specialPersonal->phone_number,
            'title' => $this->title,
            'role' => $role,
            'status' => (boolean)$specialPersonal->status,
            'image' => $path,
            'positionIn' =>  $this->roleable?->short_name,
            'competitionName' => $this->roleable_type == "App\Models\Compatition" ,
            'registeredOn' => date('Y-m-d H:m:s', strtotime($this->roleable->created_at))
        ];
    }
}
