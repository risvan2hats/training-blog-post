<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
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
            'content' => $this->content,
            'author' => $this->author->name,
            'author_id' => $this->author_id,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'image_url' => $this->image ? asset('storage/' . $this->image) : null,
            'tags' => $this->tags->map(function ($tag) {
                return ['id' => $tag->id, 'name' => $tag->name];
            }), // Return both tag ID and name
            'comments_count' => $this->comments_count ?? $this->comments->count(),
            'comments' => $this->whenLoaded('comments'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
