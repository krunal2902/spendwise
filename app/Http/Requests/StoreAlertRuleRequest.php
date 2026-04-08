<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAlertRuleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'type' => ['required', 'string', 'in:budget_threshold,low_balance,large_expense'],
            'is_active' => ['boolean']
        ];

        // Validate conditions based on type
        if ($this->input('type') === 'budget_threshold') {
            $rules['conditions.threshold_percent'] = ['required', 'numeric', 'min:1', 'max:200'];
        } elseif ($this->input('type') === 'low_balance' || $this->input('type') === 'large_expense') {
            $rules['conditions.amount'] = ['required', 'numeric', 'min:0'];
        }

        return $rules;
    }
}
