<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Services\PostService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\PostRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Http\Resources\PostCollection;

/**
 * API Controller for Post Management
 * 
 * Handles all CRUD operations for blog posts including image uploads and tag management.
 * Uses a service layer for business logic and returns standardized JSON responses.
 */
class PostController extends Controller
{
    /**
     * @var PostService $service Post service instance
     */
    protected PostService $service;

    /**
     * Constructor for PostController
     *
     * @param PostService $service Injected PostService dependency
     */
    public function __construct(PostService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a paginated listing of posts
     *
     * @return JsonResponse Standardized JSON response containing:
     * - success: boolean status
     * - data: PostCollection of posts
     * - message: Optional success message
     * @throws Exception On database or service layer errors
     */
    public function index(): JsonResponse
    {
        try {
            $posts = $this->service->getAll();
            return ResponseHelper::success(new PostCollection($posts));
        } catch (\Exception $e) {
            return ResponseHelper::error(trans('messages.post.retrieve_error'),500,[$e->getMessage()]);
        }
    }

    /**
     * Store a newly created post in storage
     *
     * @param PostRequest $request Validated post data
     * @return JsonResponse Standardized JSON response containing:
     * - success: boolean status
     * - data: PostResource of created post
     * - message: Success message
     * @throws Exception On validation, database or service layer errors
     */
    public function store(PostRequest $request): JsonResponse
    {
        try {
            $post = $this->service->createPost($request->validated());
            return ResponseHelper::created(new PostResource($post),trans('messages.post.created'));
        } catch (Exception $e) {
            return ResponseHelper::error(trans('messages.post.create_error'),500,[$e->getMessage()]);
        }
    }

    /**
     * Display the specified post
     *
     * @param int $id Post ID
     * @return JsonResponse Standardized JSON response containing:
     * - success: boolean status
     * - data: PostResource of requested post
     * - message: Optional success message
     * @throws Exception On database or service layer errors
     */
    public function show(int $id): JsonResponse
    {
        try {
            $post = $this->service->find($id);
            return ResponseHelper::success(new PostResource($post));
        } catch (Exception $e) {
            return ResponseHelper::error(trans('messages.post.retrieve_error'),500,[$e->getMessage()]);
        }
    }

    /**
     * Update the specified post in storage
     *
     * @param PostRequest $request Validated post data
     * @param int $id Post ID to update
     * @return JsonResponse Standardized JSON response containing:
     * - success: boolean status
     * - data: PostResource of updated post
     * - message: Success message
     * @throws Exception On validation, database or service layer errors
     */
    public function update(PostRequest $request, int $id): JsonResponse
    {
        try {
            $post = $this->service->updatePost($id, $request->validated());
            return ResponseHelper::success(new PostResource($post),trans('messages.post.updated'));
        } catch (Exception $e) {
            return ResponseHelper::error(trans('messages.post.update_error'),500,[$e->getMessage()]);
        }
    }

    /**
     * Remove the specified post from storage
     *
     * @param int $id Post ID to delete
     * @return JsonResponse Standardized JSON response containing:
     * - success: boolean status
     * - data: null
     * - message: Success message
     * @throws Exception On database or service layer errors
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->deletePost($id);
            return ResponseHelper::success(null,trans('messages.post.deleted'));
        } catch (Exception $e) {
            return ResponseHelper::error(trans('messages.post.delete_error'),500,[$e->getMessage()]);
        }
    }

    /**
     * Remove the image associated with the specified post
     *
     * @param int $id Post ID
     * @return JsonResponse Standardized JSON response containing:
     * - success: boolean status
     * - data: null
     * - message: Success/error message
     * @throws Exception On database or service layer errors
     */
    public function removeImage(int $id): JsonResponse
    {
        try {
            $success = $this->service->removeImage($id);
            return $success ? ResponseHelper::success(null, trans('messages.post.image_removed')) : ResponseHelper::error(trans('messages.post.no_image_exists'), 404);
        } catch (Exception $e) {
            return ResponseHelper::error(trans('messages.post.image_remove_error'), 500, [$e->getMessage()]);
        }
    }
}
