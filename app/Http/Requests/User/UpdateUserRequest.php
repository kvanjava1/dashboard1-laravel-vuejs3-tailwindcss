<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('user');

        return [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|max:255|confirmed',
            'role' => 'sometimes|required|string|exists:roles,name',
            'status' => 'sometimes|required|string|exists:user_account_statuses,name',
            'bio' => 'nullable|string|max:1000',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // 2MB maximum
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
            'role.required' => 'User role is required.',
            'role.exists' => 'Selected role does not exist.',
            'status.required' => 'Account status is required.',
            'status.in' => 'Please select a valid account status.',
            'bio.max' => 'Bio cannot exceed 1000 characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Combine first_name and last_name into a full name if both are present
        if ($this->has('first_name') && $this->has('last_name')) {
            $this->merge([
                'name' => trim($this->first_name . ' ' . $this->last_name),
            ]);
        } elseif ($this->has('first_name') || $this->has('last_name')) {
            // If only one is being updated, we need to handle this differently
            // For now, we'll skip merging name and let the service handle it
        }
    }
}