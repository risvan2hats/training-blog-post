<?php

namespace App\Repositories;

use App\Models\Comment;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * CommentRepository - Data access layer for Comment operations
 *
 * @package App\Repositories
 */
class CommentRepository extends BaseRepository
{
    /**
     * Specify the Model class name
     *
     * @return string
     */
    public function model()
    {
        return Comment::class;
    }

    /**
     * Retrieve paginated comments for a specific post
     *
     * @param int $postId The ID of the post to get comments for
     * @param int $perPage Number of comments per page (default: 10)
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getCommentsByPostId($postId, $perPage = 10)
    {
        return $this->model->with('createdBy')->where('post_id', $postId)->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Permanently delete a comment
     *
     * @param int $id The ID of the comment to delete
     * @return bool|null
     * @throws \Exception
     */
    public function deleteComment($id)
    {
        return $this->delete($id);
    }
}
