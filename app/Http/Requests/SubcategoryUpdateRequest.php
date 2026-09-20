<?php

namespace App\Http\Requests;

use App\Models\Subcategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubcategoryUpdateRequest extends FormRequest
{
    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        /** @var Subcategory $subcategory */
        $subcategory = $this->route('subcategory');

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
                    ->where('category_id', $this->input('category_id'))
                    ->ignore($subcategory),
            ],
            'icon' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', 'integer', Rule::in([0, 1])],
        ];
    }
}
