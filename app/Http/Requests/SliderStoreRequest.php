<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SliderStoreRequest extends FormRequest
{
    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'max:2048'],
            'product_id' => [
                'nullable',
                'integer',
                Rule::exists('products', 'id')->where('deleted', 0)->where('status', 1),
            ],
            'button' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'integer', Rule::in([0, 1])],
        ];
    }
}
