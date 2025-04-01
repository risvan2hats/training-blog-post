<?php

namespace App\Http\Controllers\web;

use App\Models\Tag;
use App\Models\User;
use App\Http\Controllers\Controller;

/**
 * Web Controller for Post Management
 * Handles web views related to posts and their associations
 */
class PostController extends Controller
{
    /**
     * Display the posts index page with user and tag data
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get all users and tags for dropdowns/filters
        $users  = User::all();
        $tags   = Tag::all();

        return view('posts.index', compact('users', 'tags'));
    }
}