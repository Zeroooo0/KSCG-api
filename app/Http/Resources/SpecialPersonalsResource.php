<?php

namespace App\Http\Resources;

use App\Models\Roles;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SpecialPersonalsResource extends JsonResource
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
        $roles = Roles::where('special_personals_id', $this->id)->get();
        $documents = 'embeddable';
        $rolesCollection = 'embeddable';

        if($this->image != null) {
            $path =  $storage_url . $this->image->url;
        } else {
            if($this->gender == 1) {
                $path = $storage_url . 'default/default-m-user.jpg';
            } else{
                $path = $storage_url . 'default/default-f-user.jpg';
            }
        }
        if(str_contains($request->embed, 'documents')) {
            if($this->document->first() != null) {
                $documents = DocumentsResource::collection($this->document);
            } else {
                $documents =  'Nema dokumenta';
            }
        }
        if(str_contains($request->embed, 'roles')) {
            if($roles->first() != null) {
                $rolesCollection = RolesResource::collection($roles);
            } else {
                $rolesCollection =  (string)$roles->count();
            }
        }
        if($roles->first() != null) {
            $roleInClub = new RolesResource($roles->where('roleable_type', 'App\Models\Club')->first());
        } else {
            $roleInClub =  null;
        }
        $extraData = 'Nema podataka';
        if($this->role == 1) {
            $role = 'Sudija';
            if($this->specialPersonnelForm != null) {
                $extraData = new SpecialPersonnelFormsResource($this->specialPersonnelForm);
            }

        } 
        if($this->role == 2) {
            $role = 'Trener';
            if($this->specialPersonnelForm != null) {
                $extraData = $extraData = new SpecialPersonnelFormsResource($this->specialPersonnelForm);
            }
        } 
        if($this->role == 0) {
            $role = 'Uprava';
        } 
        if($this->role == 3) {
            $role = 'Uprava';
        } 
        $kscgNo = "100000";
        $code = 'KSCG';
        $kscgNewNo = $kscgNo  + $this->id;
        $kscgId = $code . substr($kscgNewNo, 1);
      
        

        return [
            'id' => (string)$this->id,
            'kscgId' => $kscgId,
            'name' => $this->name,
            'lastName' => $this->last_name,
            'combinedName' => $this->name . ' ' . $this->last_name,
            'country' => $this->country,
            'email' => $this->email,
            'phone' => $this->phone_number,
            'role' => $role,
            'roleVal' => (string)$this->role,
            'gender' => (string)$this->gender,
            'status' => (boolean)$this->status,
            'coachIn' => $roleInClub,
            'image' => $path,
            'documents' => $documents,
            'roles' => $rolesCollection,
            'extraData' => $extraData
        ];
    }
}
