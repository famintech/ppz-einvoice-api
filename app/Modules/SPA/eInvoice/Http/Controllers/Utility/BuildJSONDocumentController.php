<?php

namespace App\Modules\SPA\eInvoice\Http\Controllers\Utility;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BuildJSONDocumentController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $document = $this->buildDocument($request);

            return response()->json([
                'status' => 200,
                'message' => 'JSON document built successfully',
                'data' => $document
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    private function buildDocument(Request $request): array
    {
        return [
            'Invoice' => [
                [
                    'ID' => [['_' => $request->input('eInvoiceCode')]],
                    'IssueDate' => [['_' => $request->input('eInvoiceDate')]],
                    'IssueTime' => [['_' => $request->input('eInvoiceTime')]],
                    'InvoiceTypeCode' => [[
                        '_' => $request->input('eInvoiceTypeCode'),
                        '@listVersionID' => $request->input('eInvoiceVersion')
                    ]],
                    'DocumentCurrencyCode' => [['_' => $request->input('currencyCode')]],
                    'TaxCurrencyCode' => $request->input('taxCurrencyCode') ? 
                        [['_' => $request->input('taxCurrencyCode')]] : null,
                    'Signature' => [[
                        'ID' => [['_' => $request->input('issuerSignature')]]
                    ]]
                ]
            ]
        ];
    }
}