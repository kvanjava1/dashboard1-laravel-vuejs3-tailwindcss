<?php
namespace App\Http\Requests\ExampleManagement;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExampleManagementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }
}
