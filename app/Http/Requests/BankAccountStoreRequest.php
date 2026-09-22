<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BankAccountStoreRequest extends FormRequest
{
    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:255', 'unique:bank_accounts,account_number'],
            'opening_balance' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'opening_balance_date' => ['nullable', 'date'],
            'is_default' => ['required', 'boolean'],
        ];
    }
}
