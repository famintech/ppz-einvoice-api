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
            'currencyCode' => 'MYR'
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