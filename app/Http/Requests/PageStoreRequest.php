<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PageStoreRequest extends FormRequest
{
    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('pages')->where('deleted', 0)],
            'eyebrow' => ['nullable', 'string', 'max:255'],
            'intro' => ['nullable', 'string'],
            'sections' => ['required', 'string'],
            'status' => ['nullable', 'integer', 'in:0,1'],
        ];
    }
}
