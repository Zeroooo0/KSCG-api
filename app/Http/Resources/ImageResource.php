<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
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
        $delete_url = env('APP_URL') . 'api/v1/delete-image/';
        return [
            'id' => (string)$this->id,
            'url' => $storage_url . $this->url,
            'deleteUrl' =>  $delete_url . $this->id
        ];
    }
}
