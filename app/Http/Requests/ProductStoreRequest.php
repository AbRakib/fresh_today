<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductStoreRequest extends FormRequest
{
    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where('deleted', 0),
            ],
            'subcategory_id' => [
                'nullable',
                'integer',
                Rule::exists('subcategories', 'id')
                    ->where('deleted', 0)
                    ->where('category_id', $this->input('category_id')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products', 'sku')],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'unit' => ['nullable', 'string', 'max:255'],
            'weight' => ['nullable', 'string', 'max:255'],
            'regular_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'lte:regular_price'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'badge' => ['nullable', 'string', 'max:255'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'minimum_order_quantity' => ['required', 'integer', 'min:1'],
            'is_featured' => ['required', 'integer', Rule::in([0, 1])],
            'status' => ['required', 'integer', Rule::in([0, 1])],
        ];
    }
}
