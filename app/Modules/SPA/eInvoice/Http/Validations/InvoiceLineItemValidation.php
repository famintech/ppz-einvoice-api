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
                'regex:/^\d{3}$/'  // Must be 3 digits as per example "001"
            ],
            'invoiceLines.*.description' => [
                'required',
                'string',
                'max:300'  // As specified in "NUMBER OF CHARS"
            ],
            'invoiceLines.*.unitPrice' => [
                'required',
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/'  // Decimal with up to 2 decimal places as per example "17.00"
            ]
        ];
    }
}