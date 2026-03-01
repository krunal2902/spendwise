<?php

namespace App\Http\Requests\Expense;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('expense')->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'account_id'   => 'required|exists:accounts,id',
            'category_id'  => 'required|exists:categories,id',
            'amount'       => 'required|numeric|min:0.01|max:9999999999.99',
            'description'  => 'nullable|string|max:255',
            'expense_date' => 'required|date|before_or_equal:today',
            'reference'    => 'nullable|string|max:100',
            'notes'        => 'nullable|string|max:1000',
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
