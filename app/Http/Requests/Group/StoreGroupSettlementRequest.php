<?php

namespace App\Http\Requests\Group;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupSettlementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paid_by'    => 'required|exists:users,id',
            'paid_to'    => 'required|exists:users,id|different:paid_by',
            'amount'     => 'required|numeric|min:0.01',
            'notes'      => 'nullable|string|max:255',
            'settled_at' => 'required|date',
        ];
    }
}
