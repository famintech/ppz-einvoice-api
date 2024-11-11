<?php

namespace App\Modules\SPA\eInvoice\Http\Controllers\Gateway;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Modules\SPA\eInvoice\Http\Controllers\Utility\BuildXMLDocumentController;
use App\Modules\SPA\eInvoice\Http\Controllers\Utility\BuildJSONDocumentController;

class ProcessInvoiceController extends Controller
{
    public function __invoke(Request $request)
    {
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
                'regex:/^([01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]Z$/',  // Format HH:MM:SSZ
                'size:9'
            ],
            'issuerSignature' => [
                'required',
                'string'
            ],
            'currencyCode' => [
                'required',
                'string',
                'size:3',
                'regex:/^[A-Z]{3}$/'  // Three uppercase letters
            ],
            'taxCurrencyCode' => [
                'nullable',
                'string',
                'size:3',
                'regex:/^[A-Z]{3}$/'
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