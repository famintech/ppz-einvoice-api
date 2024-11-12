<?php

namespace App\Modules\SPA\eInvoice\Http\Validations;

use Illuminate\Validation\Rule;

class CoreValidation
{
    public static function rules(): array
    {
        return [
            'eInvoiceVersion' => [
                'required',
                'string',
                'size:3',
                'regex:/^\d\.\d$/'
            ],
            'eInvoiceTypeCode' => [
                'required',
                'string',
                'size:2',
                'regex:/^\d{2}$/'
            ],
            'eInvoiceCode' => [
                'required',
                'string',
                'max:50'
            ],
            'eInvoiceDate' => [
                'required',
                'date_format:Y-m-d',
            ],
            'eInvoiceTime' => [
                'required',
                'regex:/^([01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]Z$/',
                'size:9'
            ],
            'currencyCode' => [
                'required',
                'string',
                'size:3',
                'regex:/^[A-Z]{3}$/'
            ],
            'billingFrequency' => [
                'nullable',
                'string',
                'max:50',
                'in:Daily,Weekly,Biweekly,Monthly,Bimonthly,Quarterly,Half-yearly,Yearly,Others,Not Applicable'
            ],
            'billingPeriodStartDate' => [
                'nullable',
                'required_with:billingFrequency',
                'date_format:Y-m-d'
            ],
            'billingPeriodEndDate' => [
                'nullable',
                'required_with:billingFrequency',
                'date_format:Y-m-d',
                'after_or_equal:billingPeriodStartDate'
            ],
            'paymentMode' => [
                'nullable',
                'string',
                'size:2',
                'regex:/^\d{2}$/'
            ],
            'supplierBankAccount' => [
                'nullable',
                'required_with:paymentMode',
                'string',
                'max:150',
                'regex:/^\d+$/'  // Only numbers allowed
            ],
            'paymentTerms' => [
                'nullable',
                'string',
                'max:300'
            ],
            'prePaymentAmount' => [
                'nullable',
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'prePaymentDate' => [
                'nullable',
                'required_with:prePaymentAmount',
                'date_format:Y-m-d'
            ],
            'prePaymentTime' => [
                'nullable',
                'required_with:prePaymentAmount',
                'regex:/^([01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]Z$/',
                'size:9'
            ],
            'prePaymentReference' => [
                'nullable',
                'required_with:prePaymentAmount',
                'string',
                'max:150'
            ],
            'billReferenceNumber' => [
                'nullable',
                'string',
                'max:150'
            ],
            'totalExcludingTax' => [
                'required',
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'totalIncludingTax' => [
                'required',
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/',
                'gte:totalExcludingTax'
            ],
            'totalPayableAmount' => [
                'required',
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/',
                'gte:totalIncludingTax'
            ],
            'totalNetAmount' => [
                'nullable',
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'totalDiscountValue' => [
                'nullable',
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'totalFeeChargeAmount' => [
                'nullable',
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'totalTaxAmount' => [
                'required',
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'roundingAmount' => [
                'nullable',
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'taxableAmountPerType' => [
                'nullable',
                'string'
            ],
            'taxAmountPerType' => [
                'required',
                'string'
            ],
            'taxExemptionDetails' => [
                'nullable',
                'required_if:totalTaxAmount,0',
                'string',
                'max:300',
                'regex:/^[.,\-()a-zA-Z0-9\s]*$/'  // Only allows period, dash, comma, parentheses, alphanumeric and spaces
            ],
            'taxExemptionCategory' => [
                'nullable',
                'required_with:taxExemptionDetails',
                'string',
                'in:E'  // For exempt category
            ],
            'taxExemptedAmount' => [
                'nullable',
                'required_if:totalTaxAmount,0',
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'taxType' => [
                'required',
                'string',
                'size:2',
                'regex:/^\d{2}$/'
            ],
            'additionalDiscountAmount' => [
                'nullable',
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'additionalDiscountReason' => [
                'nullable',
                'required_with:additionalDiscountAmount',
                'string',
                'max:300'
            ],
            'additionalFeeAmount' => [
                'nullable',
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'additionalFeeReason' => [
                'nullable',
                'required_with:additionalFeeAmount',
                'string',
                'max:300'
            ],
            'shippingRecipientName' => [
                'nullable',
                'string',
                'max:300'
            ],
            'shippingRecipientTIN' => [
                'nullable',
                'string',
                'max:14',
                'regex:/^[A-Z0-9]+$/'
            ],
            'shippingRecipientRegistration' => [
                'nullable',
                'array'
            ],
            'shippingRecipientRegistration.type' => [
                'required_with:shippingRecipientRegistration',
                'string',
                'in:NRIC,BRN,PASSPORT,ARMY'
            ],
            'shippingRecipientRegistration.number' => [
                'required_with:shippingRecipientRegistration',
                'string',
                Rule::when(fn($input) => $input['shippingRecipientRegistration']['type'] === 'NRIC', [
                    'size:12',
                    'regex:/^[0-9]+$/'
                ]),
                Rule::when(fn($input) => $input['shippingRecipientRegistration']['type'] === 'BRN', [
                    'size:20',
                    'regex:/^[0-9]+$/'
                ]),
                Rule::when(fn($input) => $input['shippingRecipientRegistration']['type'] === 'PASSPORT', [
                    'size:12',
                    'regex:/^[A-Z0-9]+$/'
                ]),
                Rule::when(fn($input) => $input['shippingRecipientRegistration']['type'] === 'ARMY', [
                    'size:12',
                    'regex:/^[A-Z0-9]+$/'
                ])
            ],
            'customsFormReference' => [
                'nullable',
                'string',
                'max:1000',
                'regex:/^[A-Z0-9,]*$/'  // Only uppercase alphanumeric and commas
            ],
            'incoterms' => [
                'nullable',
                'string',
                'size:3',
                'regex:/^[A-Z]+$/'  // Only uppercase letters
            ],
            'freeTradeAgreement' => [
                'nullable',
                'array'
            ],
            'freeTradeAgreement.name' => [
                'required_with:freeTradeAgreement',
                'string',
                'max:300',
                'regex:/^[A-Za-z0-9\s\-()]+$/' // Only alphanumeric, spaces, dashes, and parentheses
            ],
            'freeTradeAgreement.description' => [
                'nullable',
                'string',
                'max:300'
            ],
            'authorisationNumber' => [
                'nullable',
                'string',
                'max:300',
                'regex:/^[A-Za-z0-9\-]+$/' // Only alphanumeric and dashes
            ],
            'customsFormK2Reference' => [
                'nullable',
                'string',
                'max:1000',
                'regex:/^[A-Z0-9,]*$/'  // Only uppercase alphanumeric and commas
            ],
            'shippingDetails' => [
                'nullable',
                'array'
            ],
            'shippingDetails.referenceNumber' => [
                'required_with:shippingDetails',
                'string',
                'max:300'
            ],
            'shippingDetails.chargeIndicator' => [
                'required_with:shippingDetails',
                'boolean'
            ],
            'shippingDetails.amount' => [
                'required_with:shippingDetails',
                'numeric',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'shippingDetails.description' => [
                'nullable',
                'string',
                'max:300'
            ]
        ];
    }
}
