<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // You can add authorization logic here
        return true;
    }

    public function rules(): array
    {
        return [
            'display_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:255',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'string|exists:permissions,name',
            'guard_name' => 'sometimes|string|max:255',
        ];
    }
}
