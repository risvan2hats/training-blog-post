<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\CommentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider within the "api"
| middleware group. All routes in this file are protected by Sanctum
| authentication middleware. They require a valid authentication token
| to be accessed.
|
*/

// Protected API Routes - Require Sanctum Authentication
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Post Resource Routes
    Route::prefix('posts')->group(function () {
        // Main resource routes for posts
        Route::resource('/', PostController::class)->only(['index', 'store', 'show', 'update', 'destroy'])->parameters(['' => 'post']); // Set route parameter name to 'post'

        // Additional post routes not covered by resource
        Route::delete('/{post}/image', [PostController::class, 'removeImage'])->name('posts.remove-image');
        
        // Comments for posts
        Route::get('/{post}/comments', [CommentController::class, 'index'])->name('posts.comments.index');
    });

    // Comment Resource Routes
    Route::resource('comments', CommentController::class)->only(['store', 'destroy']);

    // Export posts to Excel format
    Route::get('/posts/export/excel', [ExportController::class, 'exportPosts'])->name('posts.export');
});