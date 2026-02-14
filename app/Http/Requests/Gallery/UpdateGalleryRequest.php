<?php

namespace App\Http\Requests\Gallery;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGalleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization handled via route middleware (permissions)
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'category_id' => 'sometimes|required|exists:categories,id',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:active,inactive',
            'visibility' => 'sometimes|required|in:public,private',
            'cover' => 'nullable|file|image|max:5120',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:100',
            // Optional crop coordinates for server-side crop-from-original
            'crop_canvas_width' => 'nullable|integer|min:1',
            'crop_canvas_height' => 'nullable|integer|min:1',
            'crop_x' => 'nullable|integer|min:0',
            'crop_y' => 'nullable|integer|min:0',
            'crop_width' => 'nullable|integer|min:1',
            'crop_height' => 'nullable|integer|min:1',
            'orig_width' => 'nullable|integer|min:1',
            'orig_height' => 'nullable|integer|min:1',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('visibility')) {
            $this->merge(['visibility' => $this->input('visibility')]);
        }
    }
}
