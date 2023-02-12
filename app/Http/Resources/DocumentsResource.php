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
        $delete_link = env('APP_URL') . 'api/v1/special-personal-documents-delete/' . $this->documentable_id . '?documentId=' . $this->id;
        return [
            'id' => $this->id,
            'name' => $this->name,
            'createdAt' => $this->created_at,
            'documentLink' => $storage_url . $this->doc_link,
            'deleteLink' => $delete_link
        ];
    }
}
