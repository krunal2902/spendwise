<?php

namespace App\Http\Requests\RecurringExpense;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class StoreRecurringExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id'    => 'required|exists:accounts,id',
            'category_id'   => 'required|exists:categories,id',
            'amount'        => 'required|numeric|min:0.01|max:9999999999.99',
            'description'   => 'nullable|string|max:255',
            'frequency'     => 'required|in:daily,weekly,monthly,yearly',
            'next_due_date' => 'required|date|after_or_equal:today',
            'is_active'     => 'boolean',
            'notes'         => 'nullable|string|max:1000',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->account_id) {
                $account = $this->user()->accounts()->find($this->account_id);
                if (!$account) {
                    $validator->errors()->add('account_id', 'Selected account does not belong to you.');
                }
            }

            if ($this->category_id) {
                $category = Category::forUser($this->user()->id)->find($this->category_id);
                if (!$category || $category->type !== 'expense') {
                    $validator->errors()->add('category_id', 'Selected category is not a valid expense category.');
                }
            }
        });
    }
}
