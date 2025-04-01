<?php

namespace App\Services;

use App\Repositories\PostRepository;
use Illuminate\Support\Facades\Storage;

/**
 * Service class for handling post-related operations.
 * 
 * This class provides methods for managing posts including CRUD operations,
 * image handling, and tag management.
 */
class PostService extends FilterService
{
    /**
     * Create a new PostService instance.
     *
     * @param PostRepository $repository The post repository instance
     */
    public function __construct(protected PostRepository $repository) {}

    /**
     * Get all posts with optional filtering parameters.
     *
     * @param array $params Optional filtering parameters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllPosts(array $params = [])
    {
        $inputData = $this->getInputData($params);
        $posts = $this->repository->getPostsNew();
        $posts = $this->filter($posts, $inputData);

        // return $this->repository->getPostsNew();
    }

    public function getInputData($params)
    {
        $inputData['title'] = $params['title'] ?? null;
        $inputData['author_id'] = $params['author_id'] ?? null;
        $inputData['content'] = $params['content'] ?? null;

        return $inputData;
    }

    public function filter($posts, $inputData)
    {
       $posts= $this->repository->filterWithdirectfields($posts,'author_id',$inputData['author_id']);

        return $inputData;
    }

    /**
     * Get a post by ID with its relationships.
     *
     * @param int|string $id The post ID
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getPostById($id)
    {
        return $this->repository->with(['author', 'tags', 'comments'])->find($id);
    }

    /**
     * Create a new post.
     *
     * @param array $data Post data including optional image and tags
     * @return \Illuminate\Database\Eloquent\Model The created post
     */
    public function createPost(array $data)
    {
        $post = $this->repository->create($data);
        
        if (isset($data['image'])) {
            $this->handleImageUpload($post, $data['image']);
        }
        
        if (isset($data['tags'])) {
            $post->tags()->sync($data['tags']);
        }
        
        return $post;
    }

    /**
     * Update an existing post.
     *
     * @param int|string $id The post ID
     * @param array $data Updated post data including optional image and tags
     * @return \Illuminate\Database\Eloquent\Model The updated post
     */
    public function updatePost($id, array $data)
    {
        $post = $this->repository->find($id);
        
        // Update the post first
        $this->repository->update($data, $id);
        
        // Handle image upload if provided
        if (isset($data['image'])) {
            $this->handleImageUpload($post, $data['image'], true);
        }
        
        // Sync tags if provided
        if (isset($data['tags'])) {
            $post->tags()->sync($data['tags']);
        }
        
        // Return the fresh instance of the post
        return $post->fresh();
    }

    /**
     * Delete a post and its associated image.
     *
     * @param int|string $id The post ID
     * @return bool|null
     */
    public function deletePost($id)
    {
        $post = $this->repository->find($id);
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }
        
        return $post->delete();
    }

    /**
     * Remove the image associated with a post.
     *
     * @param int|string $id The post ID
     * @return bool True if image was removed successfully
     */
    public function removeImage($id)
    {
        $post = $this->repository->find($id);
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
            $post->update(['image' => null]);
        }
        return true;
    }

    /**
     * Handle the post image upload process.
     *
     * @param \Illuminate\Database\Eloquent\Model $post The post model
     * @param \Illuminate\Http\UploadedFile $image The uploaded image file
     * @param bool $isUpdate Whether this is an update operation
     * @return void
     */
    protected function handleImageUpload($post, $image, $isUpdate = false)
    {
        if ($isUpdate && $post->image) {
            Storage::disk('public')->delete($post->image);
        }
        $path = $image->store('posts', 'public');
        $post->update(['image' => $path]);
    }
}