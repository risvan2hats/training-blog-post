<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\CommentRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\CommentCollection;

/**
 * API Controller for Comment Management
 * 
 * Handles all operations for comments including creation, retrieval, and deletion.
 * Uses a service layer for business logic and returns standardized JSON responses.
 */
class CommentController extends Controller
{
    /**
     * @var CommentService $service Comment service instance
     */
    protected CommentService $service;

    /**
     * Constructor for CommentController
     *
     * @param CommentService $service Injected CommentService dependency
     */
    public function __construct(CommentService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all comments for a specific post
     *
     * @param int $postId The post ID
     * @return JsonResponse Standardized JSON response containing:
     * - success: boolean status
     * - data: CommentCollection of comments
     * - message: Optional success message
     */
    public function index(int $postId): JsonResponse
    {
        try {
            $comments = $this->service->getCommentsByPost($postId);
            return ResponseHelper::success(new CommentCollection($comments));
        } catch (\Exception $e) {
            return ResponseHelper::error(trans('messages.comment.retrieve_error'),500,[$e->getMessage()]);
        }
    }

    /**
     * Store a newly created comment
     *
     * @param CommentRequest $request Validated comment data
     * @return JsonResponse Standardized JSON response containing:
     * - success: boolean status
     * - data: CommentResource of created comment
     * - message: Success message
     */
    public function store(CommentRequest $request): JsonResponse
    {
        try {
            $comment = $this->service->createComment($request->validated());
            return ResponseHelper::created(new CommentResource($comment),trans('messages.comment.created'));
        } catch (\Exception $e) {
            return ResponseHelper::error(trans('messages.comment.create_error'),500,[$e->getMessage()]);
        }
    }

    /**
     * Remove the specified comment
     *
     * @param int $id The comment ID
     * @return JsonResponse Standardized JSON response containing:
     * - success: boolean status
     * - data: null
     * - message: Success message
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->deleteComment($id);
            return ResponseHelper::success(null,trans('messages.comment.deleted'));
        } catch (\Exception $e) {
            return ResponseHelper::error(trans('messages.comment.delete_error'),500,[$e->getMessage()]);
        }
    }
}