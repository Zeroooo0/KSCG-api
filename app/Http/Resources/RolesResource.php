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
        $role = 'Uprava kluba';
        $val = 'clubName';
        if($this->role === 1) {
            $role = 'Sudija';
            $val = 'compatitionName';
        }
        if($this->role === 2) {
            $role = 'Trener';
        }
        $role_delete = env('APP_URL') . 'api/v1/role/';
        
        return [
            'id' => (string)$this->id,
            'name' => $specialPersonal->name . ' ' . $specialPersonal->last_name,
            'title' => $this->title,
            'role' => $role,
            'status' => (boolean)$specialPersonal->status,
            $val => $this->roleable->name,
            'registeredOn' => date('Y-m-d H:m:s', strtotime($this->roleable->created_at)),
            'deleteRequest' => $role_delete . $this->id
        ];
    }
}
