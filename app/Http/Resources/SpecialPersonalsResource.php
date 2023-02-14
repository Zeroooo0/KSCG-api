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
        if($this->image !== null) {
            $path =  $storage_url . $this->image->url;
        } else {
            if($this->gender == 'M') {
                $path = $storage_url . 'default/default-m-user.jpg';
            } else{
                $path = $storage_url . 'default/default-f-user.jpg';
            }
        }
        if($request->embed == true) {
            if($this->document->first() !== null) {
                $document_title = 'documents';
                $documents = DocumentsResource::collection($this->document);
            } else {
                $document_title = 'documents';
                $documents =  0;
            }
        } else{
            if($this->document->first() !== null) {
                $document_title = 'documents';
                $documents = count($this->document);
            } else {
                $document_title = 'documents';
                $documents =  0;
            }
        }

        return [
            'id' => $this->id,
            'basicInfo' => [
                'name' => $this->name,
                'lastName' => $this->last_name,
                'country' => $this->country,
                'email' => $this->email,
                'phone' => $this->phone_number,
                'role' => $this->role,
                'status' => (boolean)$this->status,
                'gender' => $this->gender,
                'image' => $path
            ],
            $document_title => $documents,

        ];
    }
}
