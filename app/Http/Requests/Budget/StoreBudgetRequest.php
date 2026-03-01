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
        $rules = [
            'name'          => ['required', 'string', 'max:255'],
            'amount'        => 'required|numeric|min:0.01|max:9999999999.99',
            'type'          => 'required|in:monthly,custom',
            'carry_forward' => 'boolean',
            'notes'         => 'nullable|string|max:1000',
        ];

        if ($this->type === 'custom') {
            $rules['start_date'] = 'required|date';
            $rules['end_date']   = 'required|date|after_or_equal:start_date';
            $rules['month']      = 'nullable|integer|between:1,12';
            $rules['year']       = 'nullable|integer|min:2020|max:2099';
        } else {
            $rules['month'] = 'required|integer|between:1,12';
            $rules['year']  = 'required|integer|min:2020|max:2099';
            $rules['name'][] = Rule::unique('budgets')->where(function ($query) {
                return $query->where('user_id', $this->user()->id)
                            ->where('month', $this->month)
                            ->where('year', $this->year);
            });
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.unique'            => 'You already have a budget with this name for the selected month.',
            'end_date.after_or_equal' => 'End date must be on or after the start date.',
        ];
    }
}
