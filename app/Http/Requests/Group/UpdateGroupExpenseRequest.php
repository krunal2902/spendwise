<?php

namespace App\Http\Requests\Group;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGroupExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paid_by'        => 'required|exists:users,id',
            'category_id'    => 'nullable|exists:categories,id',
            'amount'         => 'required|numeric|min:0.01',
            'description'    => 'required|string|max:255',
            'expense_date'   => 'required|date',
            'split_type'     => 'required|in:equal,exact,percentage',
            'notes'          => 'nullable|string',
            'receipt_image'  => 'nullable|image|max:2048',
            'splits'         => 'required|array',
        ];
    }
}
