<?php

return [
    'post' => [
        'title_required' => 'The post title is required.',
        'title_string' => 'The post title must be text.',
        'title_max' => 'The post title cannot exceed 255 characters.',
        
        'content_required' => 'Post content is required.',
        'content_string' => 'Post content must be text.',
        
        'author_required' => 'Please select an author.',
        'author_exists' => 'The selected author is invalid.',
        
        'date_required' => 'Publication date is required.',
        'date_valid' => 'Please enter a valid date.',
        
        'status_required' => 'Status is required.',
        'status_invalid' => 'Status must be either active or inactive.',
        
        'image_type' => 'Uploaded file must be an image.',
        'image_mimes' => 'Supported image formats: jpeg, png, jpg, gif.',
        'image_size' => 'Maximum image size is 2MB.',
    ],

    'comment' => [
        'post_id_required' => 'The post ID is required.',
        'post_id_exists' => 'The specified post does not exist.',
        'user_id_required' => 'The user ID is required.',
        'user_id_exists' => 'The specified user does not exist.',
        'content_required' => 'Comment content is required.',
        'content_min' => 'Comment must be at least 1 character.',
        'content_max' => 'Comment may not be greater than 1000 characters.',
    ],
    
    'attributes' => [
        'title' => 'post title',
        'content' => 'content',
        'author' => 'author',
        'published_at' => 'publication date',
        'status' => 'status',
        'image' => 'image',
        'post_id' => 'post',
        'user_id' => 'user',
        'content' => 'comment content',
    ],
];