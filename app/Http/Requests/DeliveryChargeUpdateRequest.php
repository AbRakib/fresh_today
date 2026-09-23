<?php

namespace App\Http\Requests;

use App\Models\DeliveryCharge;
use App\Rules\UniqueDeliveryChargeTitle;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DeliveryChargeUpdateRequest extends FormRequest
{
    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        /** @var DeliveryCharge $deliveryCharge */
        $deliveryCharge = $this->route('deliveryCharge');

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                new UniqueDeliveryChargeTitle($deliveryCharge->id),
            ],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
        ];
    }
}
