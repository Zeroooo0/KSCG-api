<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ComponentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'orderNo' => $this->order_number,
            'documents' => $request->has('embed') && str_contains($request->embed, 'documents') ? DocumentsResource::collection($this->documents->sortByDesc('id')) : 'embeddable',
            'images' => $request->has('embed') && str_contains($request->embed, 'images') ? ImageResource::collection($this->images->sortBy('order_no')) : 'embeddable',
            'roles' => $request->has('embed') && str_contains($request->embed, 'roles') ? RolesResource::collection($this->roles->sortByDesc('id')) : 'embeddable'
        ];
    }
}
