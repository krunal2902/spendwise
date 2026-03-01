<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $category = $this->route('category');

        // Block editing system categories
        if ($category->is_system) {
            return false;
        }

        // Ensure the category belongs to the user
        return $category->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'name'  => 'required|string|max:255',
            'type'  => 'required|in:income,expense',
            'icon'  => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ];
    }
}
