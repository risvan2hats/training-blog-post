<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'title'         => 'required|string|max:255',
            'content'       => 'required|string',
            'author_id'     => 'required|exists:users,id',
            'published_at'  => 'required|date',
            'status'        => 'required|in:Active,Inactive',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tags'          => 'nullable|array',
            'tags.*'        => 'exists:tags,id',
        ];

        return $rules;
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'title.required' => trans('validation.post.title_required'),
            'title.string' => trans('validation.post.title_string'),
            'title.max' => trans('validation.post.title_max'),

            'content.required' => trans('validation.post.content_required'),
            'content.string' => trans('validation.post.content_string'),

            'author_id.required' => trans('validation.post.author_required'),
            'author_id.exists' => trans('validation.post.author_exists'),

            'published_at.required' => trans('validation.post.date_required'),
            'published_at.date' => trans('validation.post.date_valid'),

            'status.required' => trans('validation.post.status_required'),
            'status.in' => trans('validation.post.status_invalid'),

            'image.image' => trans('validation.post.image_type'),
            'image.mimes' => trans('validation.post.image_mimes'),
            'image.max' => trans('validation.post.image_size'),
            
            'tags.array' => trans('validation.post.tags_array'),
            'tags.*.exists' => trans('validation.post.tag_exists'),
        ];
    }

    /**
     * Get custom validation attributes.
     */
    public function attributes(): array
    {
        return [
            'title' => trans('validation.attributes.title'),
            'content' => trans('validation.attributes.content'),
            'author_id' => trans('validation.attributes.author'),
            'published_at' => trans('validation.attributes.published_at'),
            'status' => trans('validation.attributes.status'),
            'image' => trans('validation.attributes.image'),
            'tags' => trans('validation.attributes.tags'),
            'tags.*' => trans('validation.attributes.tag'),
        ];
    }
}