<?php

namespace App\Http\Requests;

use App\Models\BankAccount;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BankAccountUpdateRequest extends FormRequest
{
    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        /** @var BankAccount $bankAccount */
        $bankAccount = $this->route('bankAccount');

        return [
            'name' => ['required', 'string', 'max:255'],
            'account_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('bank_accounts', 'account_number')->ignore($bankAccount),
            ],
            'opening_balance' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'opening_balance_date' => ['nullable', 'date'],
            'is_default' => ['required', 'boolean'],
        ];
    }
}
