<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
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
        return [
            'post_id' => 'required|integer|exists:posts,id',
            'content' => 'required|string|min:1|max:1000',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'post_id.required' => trans('validation.comment.post_id_required'),
            'post_id.exists' => trans('validation.comment.post_id_exists'),
            'content.required' => trans('validation.comment.content_required'),
            'content.min' => trans('validation.comment.content_min'),
            'content.max' => trans('validation.comment.content_max'),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'post_id' => trans('validation.attributes.post_id'),
            'user_id' => trans('validation.attributes.user_id'),
            'content' => trans('validation.attributes.content'),
        ];
    }
}