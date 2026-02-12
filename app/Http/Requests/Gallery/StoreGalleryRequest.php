<?php

namespace App\Http\Requests\Gallery;

use Illuminate\Foundation\Http\FormRequest;

class StoreGalleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization handled by route middleware (permissions)
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'visibility' => 'required|in:public,private',
            'cover' => 'required|file|image|max:5120',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:100',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Normalize inputs
        if ($this->has('visibility')) {
            $this->merge(['visibility' => $this->input('visibility')]);
        }
    }
}
