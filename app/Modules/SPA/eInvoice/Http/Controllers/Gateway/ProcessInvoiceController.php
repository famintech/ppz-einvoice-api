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
            // We'll add more validation rules based on LHDN requirements
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