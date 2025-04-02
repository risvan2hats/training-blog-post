<?php

namespace App\Services;

use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommentService extends BaseService
{
    public function __construct(Comment $model)
    {
        $this->model = $model;
    }

    /**
     * Get all comments for a specific post with pagination
     */
    public function getCommentsByPost(int $postId, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->paginateWhere(['post_id' => $postId],$perPage,['createdBy'],'created_at','desc');
    }

    /**
     * Create a new comment with authenticated user as creator
     */
    public function createComment(array $data): Comment
    {
        DB::beginTransaction();

        try {
            $data['created_by'] = Auth::id();
            $comment = $this->create($data);
            DB::commit();
            return $comment;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a comment only if created by the authenticated user
     */
    public function deleteComment(int $id): bool
    {
        DB::beginTransaction();

        try {
            $result = $this->model->where('created_by', Auth::id())->where('id', $id)->delete();
            
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}