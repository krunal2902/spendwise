<?php

namespace App\Http\Requests\Budget;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('budget')->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'categories'              => 'required|array|min:1',
            'categories.*.category_id' => [
                'required',
                'exists:categories,id',
            ],
            'categories.*.amount'     => 'required|numeric|min:0.01|max:9999999999.99',
        ];
    }

    public function messages(): array
    {
        return [
            'categories.required'                  => 'Please add at least one category budget.',
            'categories.*.category_id.required'    => 'Please select a category.',
            'categories.*.amount.required'         => 'Please enter an amount for each category.',
            'categories.*.amount.min'              => 'Each category amount must be at least ₹0.01.',
        ];
    }
}
