<?php

namespace App\Http\Requests;

use App\Rules\UniqueDeliveryChargeTitle;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DeliveryChargeStoreRequest extends FormRequest
{
    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                new UniqueDeliveryChargeTitle,
            ],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
        ];
    }
}
