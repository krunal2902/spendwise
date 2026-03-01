<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'    => 'required|string|max:255',
            'type'    => 'required|in:bank,cash,digital',
            'balance' => 'required|numeric|min:0|max:9999999999.99',
            'icon'    => 'nullable|string|max:50',
            'color'   => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ];
    }

    public function messages(): array
    {
        return [
            'color.regex' => 'Color must be a valid hex color code (e.g., #FF5733).',
        ];
    }
}
