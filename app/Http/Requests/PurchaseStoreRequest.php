<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseStoreRequest extends FormRequest
{
    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'supplier_id' => [
                'required',
                'integer',
                Rule::exists('suppliers', 'id')->where('deleted', 0)->where('status', 1),
            ],
            'purchase_date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where('deleted', 0)->where('status', 1),
            ],
            'items.*.purchase_qty' => ['required', 'integer', 'min:1'],
            'items.*.expire_date' => ['nullable', 'date'],
            'items.*.purchase_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'items.*.sell_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }
}
