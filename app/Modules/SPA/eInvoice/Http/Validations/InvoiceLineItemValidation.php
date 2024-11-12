<?php

namespace App\Modules\SPA\eInvoice\Http\Validations;

class InvoiceLineItemValidation
{
    public static function rules(): array
    {
        return [
            'invoiceLines' => [
                'required',
                'array',
                'min:1'
            ],
            'invoiceLines.*.classification' => [
                'required',
                'string',
                'size:3',
                'regex:/^\d{3}$/'  // Only 3 digits allowed
            ],
            'invoiceLines.*.description' => [
                'required',
                'string',
                'max:300'
            ],
            'invoiceLines.*.unitPrice' => [
                'required',
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/'  // Decimal with up to 2 decimal places
            ],
            'invoiceLines.*.quantity' => [
                'required',
                'numeric',
                'min:0',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'invoiceLines.*.unitCode' => [
                'required',
                'string',
                'max:3'
            ],
            'invoiceLines.*.lineAmount' => [
                'required',
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/'
            ]
        ];
    }
}