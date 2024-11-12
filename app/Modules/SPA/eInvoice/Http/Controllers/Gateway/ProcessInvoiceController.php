<?php

namespace App\Modules\SPA\eInvoice\Http\Controllers\Gateway;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Modules\SPA\eInvoice\Http\Controllers\Utility\BuildXMLDocumentController;
use App\Modules\SPA\eInvoice\Http\Controllers\Utility\BuildJSONDocumentController;
use Carbon\Carbon;

class ProcessInvoiceController extends Controller
{
    public function __invoke(Request $request)
    {
        // Set KL timezone and get current time
        $now = Carbon::now('Asia/Kuala_Lumpur');

        // Merge current date and time into request
        $request->merge([
            'eInvoiceDate' => $now->format('Y-m-d'),
            'eInvoiceTime' => $now->format('H:i:s') . 'Z',
            'eInvoiceVersion' => '1.0',
            'currencyCode' => 'MYR',
            // 'taxCurrencyCode' => 'MYR'
        ]);

        $validator = Validator::make($request->all(), [
            'format' => 'required|in:XML,JSON',
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
            // 'taxCurrencyCode' => [
            //     'nullable',
            //     'string',
            //     'size:3',
            //     'regex:/^[A-Z]{3}$/'
            // ]
            // New billing period validations
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
            'shippingRecipientAddress' => [
                'nullable',
                'required_with:shippingRecipientName',
                'array'
            ],
            'shippingRecipientAddress.streetName' => [
                'required_with:shippingRecipientAddress',
                'string',
                'max:300'
            ],
            'shippingRecipientAddress.cityName' => [
                'required_with:shippingRecipientAddress',
                'string',
                'max:50'
            ],
            'shippingRecipientAddress.postalZone' => [
                'required_with:shippingRecipientAddress',
                'string',
                'max:10'
            ],
            'shippingRecipientTIN' => [
                'nullable',
                'string',
                'max:14',
                'regex:/^[A-Z0-9]+$/' 
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'status' => 400
            ], 400);
        }

        try {
            $controller = $request->input('format') === 'XML'
                ? BuildXMLDocumentController::class
                : BuildJSONDocumentController::class;

            return app($controller)->__invoke($request);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }
}
