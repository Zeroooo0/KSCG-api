<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DocumentsResource extends JsonResource
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
        return [
            'id' => (string)$this->id,
            'name' => $this->name,
            'createdAt' => date($this->created_at),
            'documentLink' => $storage_url . $this->doc_link,
        ];
    }
}
