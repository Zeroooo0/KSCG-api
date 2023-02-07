<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
        $image_url = env('APP_URL') . 'api/image/';
        if($this->image !== null) {
            $path =  $image_url . $this->image->url;
        } else {
            if($this->gender == 'M') {
                $path = $image_url . 'default/default-m-user.jpg';
            } else{
                $path = $image_url . 'default/default-f-user.jpg';
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
                'rolle' => $this->rolle,
                'status' => (boolean)$this->status,
                'gender' => $this->gender,
                'image' => $path
            ]
        ];
    }
}
