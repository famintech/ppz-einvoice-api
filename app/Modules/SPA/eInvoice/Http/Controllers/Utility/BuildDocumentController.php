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

            if ($request->input('download') && $request->input('format') === 'XML') {
                return response($document, 200, [
                    'Content-Type' => 'application/xml',
                    'Content-Disposition' => 'attachment; filename="invoice.xml"',
                ]);
            }

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
        // Create XML with root namespace
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
        <Invoice xmlns="' . self::XML_NAMESPACES['xmlns'] . '"
                xmlns:cac="' . self::XML_NAMESPACES['xmlns:cac'] . '"
                xmlns:cbc="' . self::XML_NAMESPACES['xmlns:cbc'] . '"/>');

        // Add core elements with correct namespace prefixes
        $xml->addChild('cbc:ID', $request->input('eInvoiceCode'), self::XML_NAMESPACES['xmlns:cbc']);

        $typeCode = $xml->addChild('cbc:InvoiceTypeCode', $request->input('eInvoiceTypeCode'), self::XML_NAMESPACES['xmlns:cbc']);
        $typeCode->addAttribute('listVersionID', $request->input('eInvoiceVersion'));

        // Return formatted XML
        $dom = new \DOMDocument('1.0', 'UTF-8');
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
