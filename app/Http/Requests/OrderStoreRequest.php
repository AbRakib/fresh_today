<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')->where('deleted', 0)->where('status', 1)],
            'delivery_charge_id' => ['required', 'integer', Rule::exists('delivery_charges', 'id')->where('deleted', 0)->where('status', 1)],
            'order_date' => ['required', 'date'],
            'delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'delivery_address' => ['nullable', 'string'],
            'note' => ['nullable', 'string'],
            'discount_amount' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'delivery_charge' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', Rule::exists('products', 'id')->where('deleted', 0)->where('status', 1)],
            'items.*.order_qty' => ['required', 'integer', 'min:1'],
            'items.*.regular_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'items.*.sale_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'items.*.discount_amount' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }
}
