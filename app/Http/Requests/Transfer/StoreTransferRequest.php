<?php

namespace App\Http\Requests\Transfer;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id'   => 'required|exists:accounts,id|different:from_account_id',
            'amount'          => 'required|numeric|min:0.01|max:9999999999.99',
            'description'     => 'nullable|string|max:255',
            'transfer_date'   => 'required|date|before_or_equal:today',
            'reference'       => 'nullable|string|max:100',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();

            if ($this->from_account_id && !$user->accounts()->where('id', $this->from_account_id)->exists()) {
                $validator->errors()->add('from_account_id', 'Source account does not belong to you.');
            }

            if ($this->to_account_id && !$user->accounts()->where('id', $this->to_account_id)->exists()) {
                $validator->errors()->add('to_account_id', 'Destination account does not belong to you.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'to_account_id.different' => 'Source and destination accounts must be different.',
        ];
    }
}
