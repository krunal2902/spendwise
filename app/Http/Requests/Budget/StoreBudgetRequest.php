<?php

namespace App\Http\Requests\Budget;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'   => [
                'required',
                'string',
                'max:255',
                Rule::unique('budgets')->where(function ($query) {
                    return $query->where('user_id', $this->user()->id)
                                ->where('month', $this->month)
                                ->where('year', $this->year);
                }),
            ],
            'amount' => 'required|numeric|min:0.01|max:9999999999.99',
            'month'  => 'required|integer|between:1,12',
            'year'   => 'required|integer|min:2020|max:2099',
            'notes'  => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'You already have a budget with this name for the selected month.',
        ];
    }
}
