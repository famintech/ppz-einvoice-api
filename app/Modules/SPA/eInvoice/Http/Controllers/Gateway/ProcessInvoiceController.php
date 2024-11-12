<?php

namespace App\Modules\SPA\eInvoice\Http\Controllers\Gateway;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Modules\SPA\eInvoice\Http\Controllers\Utility\BuildXMLDocumentController;
use App\Modules\SPA\eInvoice\Http\Controllers\Utility\BuildJSONDocumentController;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Modules\SPA\eInvoice\Http\Validations\CoreValidation;
use App\Modules\SPA\eInvoice\Http\Validations\InvoiceLineItemValidation;
use App\Modules\SPA\eInvoice\Http\Validations\SupplierValidation;
use App\Modules\SPA\eInvoice\Http\Validations\BuyerValidation;
use App\Modules\SPA\eInvoice\Http\Validations\AddressValidation;

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
        ]);

        // 'taxCurrencyCode' => [
            //     'nullable',
            //     'string',
            //     'size:3',
            //     'regex:/^[A-Z]{3}$/'
            // ]
            // New billing period validations

        $validator = Validator::make($request->all(), [
            'format' => 'required|in:XML,JSON',
            ...CoreValidation::rules(),
            ...AddressValidation::rules(),
            ...InvoiceLineItemValidation::rules(),
            ...SupplierValidation::rules(),
            ...BuyerValidation::rules(),
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
