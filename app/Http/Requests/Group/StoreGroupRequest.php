<?php

namespace App\Http\Requests\Group;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'currency'    => 'nullable|string|max:10',
            'image'       => 'nullable|image|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Group name is required.',
            'name.max'      => 'Group name cannot exceed 255 characters.',
            'image.image'   => 'The file must be an image.',
            'image.max'     => 'Image size cannot exceed 2MB.',
        ];
    }
}
