<?php

namespace App\Modules\SPA\eInvoice\Http\Controllers\Gateway;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Modules\SPA\eInvoice\Http\Controllers\Utility\BuildDocumentController;

class ProcessInvoiceController extends Controller
{
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'format' => 'required|in:XML,JSON',
            // 'supplier' => 'required|array',
            // 'buyer' => 'required|array',
            'eInvoiceVersion' => [
                'required',
                'string',
                'size:3',  // To match "1.0" format
                'regex:/^\d\.\d$/'  // Ensures format like "1.0", "2.0"
            ],
            'eInvoiceTypeCode' => [
                'required',
                'string',
                'size:2',  // For "01" format
                'regex:/^\d{2}$/'  // Must be 2 digits
            ],
            'eInvoiceCode' => [
                'required',
                'string',
                'max:50'  // As per NUMBER OF CHARS column
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'status' => 400
            ], 400);
        }

        try {
            return app(BuildDocumentController::class)->__invoke($request);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }
}