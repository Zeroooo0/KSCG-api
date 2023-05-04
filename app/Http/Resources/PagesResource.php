<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PagesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        date_default_timezone_set('Europe/Amsterdam');
        $userData = null;
        if($this->user != null) {
            $userData = [
                'id' => (string)$this->user->id,
                'name' => $this->user->name,
                'lastName' => $this->user->last_name
            ];
        }
        $image = null;
        if($this->images != []) {
           $image = ImageResource::collection($this->images);
        }
        $cover_image = $this->cover_image;
        if($this->cover_image != null) {
            $cover_image = new ImageResource($this->images->where('id', $this->cover_image)->first());
        }
        $components = $this->components->sortBy('order_number');
        $assambly = [
            'title'=> 'SKUPŠTINA',
            'roles' => [],
            'components' => ComponentResource::collection($components->where('type', 'tab-assembly')),
        ];
        $componentsCollection =  ComponentResource::collection($components->where('type', 'tab-roles'));
        $commission = [
            'title'=> 'SKUPŠTINA',
            'roles' => [],
            'components' => ComponentResource::collection($components->where('type', 'tab-commission')),
        ];
        $judes = [
            'title'=> 'SKUPŠTINA',
            'roles' => [],
            'components' => ComponentResource::collection($components->where('type', 'tab-judicial-organization')),
        ];
        return [
            'id' => (string)$this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'createdAt' => date($this->created_at),
            'updatedAt' => date($this->updated_at),
            'user' => $userData,
            'coverImage' => $cover_image,
            'images' => $image,
            'components' => [$assambly, $componentsCollection, $commission, $judes],

        ];
    }
}
