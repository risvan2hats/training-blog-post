<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Repositories\CommentRepository;

/**
 * Service class for handling comment-related operations.
 * 
 * This class provides methods for managing comments including creation,
 * retrieval, and deletion while handling business logic.
 */
class CommentService
{
    /**
     * Create a new CommentService instance.
     *
     * @param CommentRepository $repository The comment repository instance
     */
    public function __construct(protected CommentRepository $repository) {}

    /**
     * Get all comments for a specific post.
     *
     * @param int $postId The post ID
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCommentsByPost(int $postId)
    {
        return $this->repository->getCommentsByPostId($postId);
    }

    /**
     * Create a new comment.
     *
     * @param array $data Comment data including content and post_id
     * @return \Illuminate\Database\Eloquent\Model The created comment
     */
    public function createComment(array $data)
    {
        $data['created_by'] = Auth::id();
        return $this->repository->create($data);
    }

    /**
     * Delete a comment.
     *
     * @param int $id The comment ID
     * @return bool|null
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteComment(int $id)
    {
        return $this->repository->where('created_by',Auth::id())->where('id',$id)->delete();
    }
}