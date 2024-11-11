<?php

namespace App\Modules\SPA\eInvoice\Http\Controllers\Utility;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SimpleXMLElement;

class BuildXMLDocumentController extends Controller
{
    private const XML_NAMESPACES = [
        'xmlns' => "urn:oasis:names:specification:ubl:schema:xsd:Invoice-2",
        'xmlns:cac' => "urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2",
        'xmlns:cbc' => "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
    ];

    public function __invoke(Request $request)
    {
        try {
            $document = $this->buildDocument($request);

            if ($request->input('download')) {
                return response($document, 200, [
                    'Content-Type' => 'application/xml',
                    'Content-Disposition' => 'attachment; filename="invoice.xml"',
                ]);
            }

            return response()->json([
                'status' => 200,
                'message' => 'XML document built successfully',
                'data' => $document
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    private function buildDocument(Request $request): string
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
            <Invoice xmlns="' . self::XML_NAMESPACES['xmlns'] . '"
                    xmlns:cac="' . self::XML_NAMESPACES['xmlns:cac'] . '"
                    xmlns:cbc="' . self::XML_NAMESPACES['xmlns:cbc'] . '"/>');

        // Core elements
        $xml->addChild('cbc:ID', $request->input('eInvoiceCode'), self::XML_NAMESPACES['xmlns:cbc']);
        $xml->addChild('cbc:IssueDate', $request->input('eInvoiceDate'), self::XML_NAMESPACES['xmlns:cbc']);
        $xml->addChild('cbc:IssueTime', $request->input('eInvoiceTime'), self::XML_NAMESPACES['xmlns:cbc']);

        $typeCode = $xml->addChild('cbc:InvoiceTypeCode', $request->input('eInvoiceTypeCode'), self::XML_NAMESPACES['xmlns:cbc']);
        $typeCode->addAttribute('listVersionID', $request->input('eInvoiceVersion'));

        $xml->addChild('cbc:DocumentCurrencyCode', $request->input('currencyCode'), self::XML_NAMESPACES['xmlns:cbc']);

        if ($request->input('taxCurrencyCode')) {
            $xml->addChild('cbc:TaxCurrencyCode', $request->input('taxCurrencyCode'), self::XML_NAMESPACES['xmlns:cbc']);
        }

        // Digital Signature
        // $signature = $xml->addChild('cac:Signature', null, self::XML_NAMESPACES['xmlns:cac']);
        // $signature->addChild('cbc:ID', $request->input('issuerSignature'), self::XML_NAMESPACES['xmlns:cbc']);

        // Format and return
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        return $dom->saveXML();
    }
}