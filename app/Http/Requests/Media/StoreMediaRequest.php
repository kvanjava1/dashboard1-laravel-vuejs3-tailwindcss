<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gallery_id' => 'nullable|exists:galleries,id',
            'alt_text' => 'nullable|string|max:255',
            'file' => 'required|file|image|max:5120',
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
}
