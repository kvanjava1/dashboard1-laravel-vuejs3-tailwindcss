<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Will be checked via middleware/permission
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,NULL,id,deleted_at,NULL|max:255',
            'password' => 'required|string|min:8|max:255|confirmed',
            'role' => 'required|string|exists:roles,name',
            'phone' => 'nullable|string|max:20',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // 2MB maximum
            // Optional crop coordinates for server-side crop-from-original
            'crop_canvas_width' => 'nullable|integer|min:1',
            'crop_canvas_height' => 'nullable|integer|min:1',
            'crop_x' => 'nullable|integer|min:0',
            'crop_y' => 'nullable|integer|min:0',
            'crop_width' => 'nullable|integer|min:1',
            'crop_height' => 'nullable|integer|min:1',
            'orig_width' => 'nullable|integer|min:1',
            'orig_height' => 'nullable|integer|min:1',
            'is_banned' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Full name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
            'role.required' => 'Please select a user role.',
            'role.exists' => 'The selected role is invalid.',
        ];
    }
}