<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostsResource extends JsonResource
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
        $storage_url = env('APP_URL') . 'api/file/';
        $userData = $this->user_id;
        if($this->user_id != null) {
            $userData = [
                'id' => (string)$this->user->id,
                'name' => 'Karate Savez',
                'lastName' => 'Crne Gore'
            ];
        }
        $image = null;
        if($this->images != []) {
           $image = ImageResource::collection($this->images);
        }
        $cover_image = [
            'url' => $storage_url . 'default/default-news-poster.jpg'
        ];
        if($this->cover_image != null) {
            $cover_image = new ImageResource($this->images->where('id', $this->cover_image)->first());
        }
        return [
            'id' => (string)$this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'gallery' => (boolean)$this->gallery,
            'createdAt' => date($this->created_at),
            'updatedAt' => date($this->updated_at),
            'user' => $userData,
            'coverImage' => $cover_image,
            'images' => $image,
            'components' => ComponentResource::collection($this->components)
        ];
    }
}
