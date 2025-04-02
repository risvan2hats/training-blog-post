<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostService extends BaseService
{
    use FilterService;

    protected $model;
    public $params = [];

    protected array $searchColumns  = ['title', 'content'];
    protected array $filterMap      = [
        'title'         => ['type'  => 'string',    'condition' => 'like'],
        'content'       => ['type'  => 'string',    'condition' => 'like'],
        'status'        => ['type'  => 'string',    'condition' => '='  ],
        'author_ids'    => ['type'  => 'int',       'condition' => 'in', 'key'   => 'author_id'],
        'tag_ids'       => ['type'  => 'int',       'condition' => 'in', 'key'   => 'tags.id', 'whereHas' => 'tags'],
        'date_from'     => ['type'  => 'date',      'condition' => '>=', 'key'   => 'published_at'],
        'date_to'       => ['type'  => 'date',      'condition' => '<=', 'key'   => 'published_at'],
    ];

    public function __construct(Post $model)
    {
        $this->model = $model;
    }

    /**
     * Get filtered posts with pagination
     */
    public function getAll()
    {
        $this->params['with'] = ['author', 'comments', 'tags'];
        return $this->getAllFiltered();
    }

    /**
     * Create post with image and tags handling
     */
    public function createPost(array $data): Post
    {
        DB::beginTransaction();

        try {
            $post = $this->create($data);
            
            if (isset($data['image'])) {
                $this->handleImageUpload($post, $data['image']);
            }
            
            if (isset($data['tags'])) {
                $post->tags()->sync($data['tags']);
            }
            
            DB::commit();
            return $post;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update post with image and tags handling
     */
    public function updatePost($id, array $data): Post
    {
        DB::beginTransaction();

        try {
            $post = $this->update($id, $data);
            
            if (isset($data['image'])) {
                $this->handleImageUpload($post, $data['image'], true);
            }
            
            if (isset($data['tags'])) {
                $post->tags()->sync($data['tags']);
            }
            
            DB::commit();
            return $post;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete post with image cleanup
     */
    public function deletePost($id): bool
    {
        DB::beginTransaction();

        try {
            $post = $this->find($id);
            
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            
            $result = $this->delete($id);
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Handle image upload for posts
     */
    protected function handleImageUpload($post, $image, $isUpdate = false): void
    {
        if ($isUpdate && $post->image) {
            Storage::disk('public')->delete($post->image);
        }
        $path = $image->store('posts', 'public');
        $post->update(['image' => $path]);
    }

    /**
     * Remove post image
     */
    public function removeImage($id): bool
    {
        DB::beginTransaction();

        try {
            $post = $this->find($id);
            
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
                $post->update(['image' => null]);
            }
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getPostsForExport(array $filters = [])
    {
        $this->params['with'] = ['author', 'comments', 'tags'];
        return $this->getAllFiltered(false);
    }
}