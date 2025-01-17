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

            if ($request->input('download')) {
                return response($document, 200, [
                    'Content-Type' => 'application/json',
                    'Content-Disposition' => 'attachment; filename="invoice.json"',
                ]);
            }

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
        '_D' => "urn:oasis:names:specification:ubl:schema:xsd:Invoice-2",
        '_A' => "urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2",
        '_B' => "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2",
        'Invoice' => [
            [
                'ID' => [
                    [
                        '_' => $request->input('eInvoiceCode')
                    ]
                ],
                'IssueDate' => [
                    [
                        '_' => $request->input('eInvoiceDate')
                    ]
                ],
                'IssueTime' => [
                    [
                        '_' => $request->input('eInvoiceTime')
                    ]
                ],
                'InvoiceTypeCode' => [
                    [
                        '_' => $request->input('eInvoiceTypeCode'),
                        'listVersionID' => $request->input('eInvoiceVersion') 
                    ]
                ],
                'DocumentCurrencyCode' => [
                    [
                        '_' => $request->input('currencyCode')
                    ]
                ],
                // 'TaxCurrencyCode' => $request->input('taxCurrencyCode') ? 
                //     [
                //         [
                //             '_' => $request->input('taxCurrencyCode')
                //         ]
                //     ] : null
            ]
        ]
    ];
}
}