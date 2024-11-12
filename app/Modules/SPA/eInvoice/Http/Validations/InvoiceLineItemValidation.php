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
            ],
            'invoiceLines.*.taxType' => [
                'required',
                'string',
                'size:2',
                'regex:/^\d{2}$/'  // Must be 2 digits as per example "01"
            ],
            'invoiceLines.*.taxRate' => [
                'required_if:invoiceLines.*.taxType,01',  // Required when tax type is specified
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/',  // Decimal with up to 2 decimal places as per example "10"
                'min:0'
            ],
            'invoiceLines.*.taxAmount' => [
                'required',
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/'  // Decimal with up to 2 decimal places
            ],
            'invoiceLines.*.taxExemptionDetails' => [
                'nullable',
                'required_if:invoiceLines.*.taxAmount,0',
                'string',
                'max:300',
                'regex:/^[.,\-()a-zA-Z0-9\s]*$/'  // Only allows period, dash, comma, parentheses, alphanumeric and spaces
            ],
            'invoiceLines.*.subtotal' => [
                'required',
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/'  // Decimal with up to 2 decimal places
            ],
            'invoiceLines.*.taxExemptedAmount' => [
                'nullable',
                'required_if:invoiceLines.*.taxExemptionDetails,!=,null',
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/'  // Decimal with up to 2 decimal places
            ],
            'invoiceLines.*.totalExcludingTax' => [
                'required',
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/'  // Decimal with up to 2 decimal places
            ],
            'invoiceLines.*.quantity' => [
                'nullable',
                'numeric',
                'regex:/^\d+(\.\d{0,5})?$/'  // Decimal with up to 5 decimal places as recommended
            ],
            'invoiceLines.*.measurementUnit' => [
                'nullable',
                'required_with:invoiceLines.*.quantity',
                'string',
                'size:3'  // Following UN/ECE Recommendation 20
            ],
            'invoiceLines.*.discountRate' => [
                'nullable',
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/',  // Decimal with up to 2 decimal places
                'min:0',
                'max:1'  // As it's a percentage in decimal form (0.15 = 15%)
            ],
            'invoiceLines.*.discountAmount' => [
                'nullable',
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/'  // Decimal with up to 2 decimal places
            ],
            'invoiceLines.*.discountReason' => [
                'nullable',
                'required_with:invoiceLines.*.discountAmount',
                'string',
                'max:300'
            ],
            'invoiceLines.*.feeRate' => [
                'nullable',
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/',  // Decimal with up to 2 decimal places
                'min:0',
                'max:1'  // As it's a percentage in decimal form (0.10 = 10%)
            ],
            'invoiceLines.*.feeAmount' => [
                'nullable',
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/'  // Decimal with up to 2 decimal places
            ],
            'invoiceLines.*.feeReason' => [
                'nullable',
                'required_with:invoiceLines.*.feeAmount',
                'string',
                'max:300'
            ],
            'invoiceLines.*.tariffCode' => [
                'nullable',
                'string',
                'size:12',  // Based on example "9800.00.0010"
                'regex:/^\d{4}\.\d{2}\.\d{4}$/'
            ],
            'invoiceLines.*.countryOfOrigin' => [
                'nullable',
                'string',
                'size:3'  // Based on example "GBR"
            ]
        ];
    }
}
