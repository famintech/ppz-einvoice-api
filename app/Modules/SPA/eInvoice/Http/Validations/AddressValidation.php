<?php

namespace App\Modules\SPA\eInvoice\Http\Validations;

class AddressValidation
{
    public static function rules(): array
    {
        return [
            'shippingRecipientAddress' => [
                'nullable',
                'required_with:shippingRecipientName',
                'array'
            ],
            'shippingRecipientAddress.line0' => [
                'required_with:shippingRecipientAddress',
                'string',
                'max:150'
            ],
            'shippingRecipientAddress.line1' => [
                'nullable',
                'string',
                'max:150'
            ],
            'shippingRecipientAddress.line2' => [
                'nullable',
                'string',
                'max:150'
            ],
            'shippingRecipientAddress.cityName' => [
                'required_with:shippingRecipientAddress',
                'string',
                'max:50'
            ],
            'shippingRecipientAddress.postalZone' => [
                'nullable',
                'string',
                'max:50'
            ],
            'shippingRecipientAddress.state' => [
                'required_with:shippingRecipientAddress',
                'string',
                'max:50'
            ],
            'shippingRecipientAddress.country' => [
                'required_with:shippingRecipientAddress',
                'string',
                'size:3',
                'regex:/^[A-Z]{3}$/'  // Three-letter country code
            ],
        ];
    }
}
