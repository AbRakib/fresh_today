<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubcategoryStoreRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('subcategories', 'name')
                    ->where('deleted', 0)
                    ->where('category_id', $this->input('category_id')),
            ],
            'icon' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', 'integer', Rule::in([0, 1])],
        ];
    }
}
