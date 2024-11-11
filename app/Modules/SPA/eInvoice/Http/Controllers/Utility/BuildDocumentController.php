<?php

namespace App\Modules\SPA\eInvoice\Http\Controllers\Utility;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SimpleXMLElement;

class BuildDocumentController extends Controller
{
    private const XML_NAMESPACES = [
        'xmlns' => "urn:oasis:names:specification:ubl:schema:xsd:Invoice-2",
        'xmlns:cac' => "urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2",
        'xmlns:cbc' => "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
    ];

    public function __invoke(Request $request)
    {
        try {
            $document = $request->input('format') === 'XML' 
                ? $this->buildXMLDocument($request)
                : $this->buildJSONDocument($request);

            return response()->json([
                'status' => 200,
                'message' => 'Document built successfully',
                'data' => $document
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    private function buildXMLDocument(Request $request): string
    {
        $xml = new SimpleXMLElement('<Invoice/>');
        
        // Add namespaces
        foreach (self::XML_NAMESPACES as $key => $value) {
            $xml->addAttribute($key, $value);
        }

        // Add core elements
        $xml->addChild('cbc:ID', $request->input('eInvoiceCode'));
        
        $typeCode = $xml->addChild('cbc:InvoiceTypeCode', $request->input('eInvoiceTypeCode'));
        $typeCode->addAttribute('listVersionID', $request->input('eInvoiceVersion'));

        // Return formatted XML
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        
        return $dom->saveXML();
    }

    private function buildJSONDocument(Request $request): array
    {
        return [
            'Invoice' => [
                [
                    'ID' => [
                        [
                            '_' => $request->input('eInvoiceCode')
                        ]
                    ],
                    'InvoiceTypeCode' => [
                        [
                            '_' => $request->input('eInvoiceTypeCode'),
                            '@listVersionID' => $request->input('eInvoiceVersion')
                        ]
                    ]
                ]
            ]
        ];
    }
}