<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PostCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'content' => $post->content,
                    'author' => $post->author->name,
                    'author_id' => $post->author_id,
                    'status' => $post->status,
                    'published_at' => $post->published_at ? $post->published_at->format('d/m/Y h:i A') : null,
                    'image_url' => $post->image ? asset('storage/' . $post->image) : null,
                    'tags' => $post->tags->pluck('name')->toArray(), // Fetch tag names properly
                    'comments_count' => $post->comments_count ?? $post->comments->count(),
                    'comments' => $post->whenLoaded('comments'),
                    'created_at' => $post->created_at ? $post->created_at->format('Y-m-d H:i:s') : null,
                    'updated_at' => $post->updated_at ? $post->updated_at->format('Y-m-d H:i:s') : null,
                ];
            }),
            'links' => [
                'first' => $this->url(1),
                'last' => $this->url($this->lastPage()),
                'prev' => $this->previousPageUrl(),
                'next' => $this->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $this->currentPage(),
                'from' => $this->firstItem(),
                'last_page' => $this->lastPage(),
                'path' => $this->path(),
                'per_page' => $this->perPage(),
                'to' => $this->lastItem(),
                'total' => $this->total(),
            ],
        ];
    }
}
