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

        // Add Invoice Period if billing frequency is provided
        if ($request->input('billingFrequency')) {
            $invoicePeriod = $xml->addChild('cac:InvoicePeriod', null, self::XML_NAMESPACES['xmlns:cac']);
            $invoicePeriod->addChild('cbc:StartDate', $request->input('billingPeriodStartDate'), self::XML_NAMESPACES['xmlns:cbc']);
            $invoicePeriod->addChild('cbc:EndDate', $request->input('billingPeriodEndDate'), self::XML_NAMESPACES['xmlns:cbc']);
            $invoicePeriod->addChild('cbc:Description', $request->input('billingFrequency'), self::XML_NAMESPACES['xmlns:cbc']);
        }

        // Add Payment Means if payment mode is provided
        if ($request->input('paymentMode')) {
            $paymentMeans = $xml->addChild('cac:PaymentMeans', null, self::XML_NAMESPACES['xmlns:cac']);
            $paymentMeans->addChild('cbc:PaymentMeansCode', $request->input('paymentMode'), self::XML_NAMESPACES['xmlns:cbc']);
        }

        // Add Payment Means if payment mode is provided
        if ($request->input('paymentMode')) {
            $paymentMeans = $xml->addChild('cac:PaymentMeans', null, self::XML_NAMESPACES['xmlns:cac']);
            $paymentMeans->addChild('cbc:PaymentMeansCode', $request->input('paymentMode'), self::XML_NAMESPACES['xmlns:cbc']);

            // Add bank account if provided
            if ($request->input('supplierBankAccount')) {
                $payeeFinancialAccount = $paymentMeans->addChild('cac:PayeeFinancialAccount', null, self::XML_NAMESPACES['xmlns:cac']);
                $payeeFinancialAccount->addChild('cbc:ID', $request->input('supplierBankAccount'), self::XML_NAMESPACES['xmlns:cbc']);
            }
        }

        // Add Payment Terms if provided
        if ($request->input('paymentTerms')) {
            $paymentTerms = $xml->addChild('cac:PaymentTerms', null, self::XML_NAMESPACES['xmlns:cac']);
            $paymentTerms->addChild('cbc:Note', $request->input('paymentTerms'), self::XML_NAMESPACES['xmlns:cbc']);
        }

        // Add Prepayment information if amount is provided
        if ($request->input('prePaymentAmount')) {
            $prepaidPayment = $xml->addChild('cac:PrepaidPayment', null, self::XML_NAMESPACES['xmlns:cac']);

            $paidAmount = $prepaidPayment->addChild('cbc:PaidAmount', $request->input('prePaymentAmount'), self::XML_NAMESPACES['xmlns:cbc']);
            $paidAmount->addAttribute('currencyID', $request->input('currencyCode'));

            $prepaidPayment->addChild('cbc:PaidDate', $request->input('prePaymentDate'), self::XML_NAMESPACES['xmlns:cbc']);
            $prepaidPayment->addChild('cbc:PaidTime', $request->input('prePaymentTime'), self::XML_NAMESPACES['xmlns:cbc']);
            $prepaidPayment->addChild('cbc:ID', $request->input('prePaymentReference'), self::XML_NAMESPACES['xmlns:cbc']);
        }

        // Add Billing Reference if provided
        if ($request->input('billReferenceNumber')) {
            $billingReference = $xml->addChild('cac:BillingReference', null, self::XML_NAMESPACES['xmlns:cac']);
            $additionalDocumentReference = $billingReference->addChild('cac:AdditionalDocumentReference', null, self::XML_NAMESPACES['xmlns:cac']);
            $additionalDocumentReference->addChild('cbc:ID', $request->input('billReferenceNumber'), self::XML_NAMESPACES['xmlns:cbc']);
        }

        // Add Legal Monetary Totals (mandatory)
        $legalMonetaryTotal = $xml->addChild('cac:LegalMonetaryTotal', null, self::XML_NAMESPACES['xmlns:cac']);

        $taxExclusiveAmount = $legalMonetaryTotal->addChild('cbc:TaxExclusiveAmount', $request->input('totalExcludingTax'), self::XML_NAMESPACES['xmlns:cbc']);
        $taxExclusiveAmount->addAttribute('currencyID', $request->input('currencyCode'));

        $taxInclusiveAmount = $legalMonetaryTotal->addChild('cbc:TaxInclusiveAmount', $request->input('totalIncludingTax'), self::XML_NAMESPACES['xmlns:cbc']);
        $taxInclusiveAmount->addAttribute('currencyID', $request->input('currencyCode'));

        // Add PayableAmount (mandatory)
        $payableAmount = $legalMonetaryTotal->addChild('cbc:PayableAmount', $request->input('totalPayableAmount'), self::XML_NAMESPACES['xmlns:cbc']);
        $payableAmount->addAttribute('currencyID', $request->input('currencyCode'));

        // Add LineExtensionAmount if totalNetAmount is provided
        if ($request->input('totalNetAmount')) {
            $lineExtensionAmount = $legalMonetaryTotal->addChild('cbc:LineExtensionAmount', $request->input('totalNetAmount'), self::XML_NAMESPACES['xmlns:cbc']);
            $lineExtensionAmount->addAttribute('currencyID', $request->input('currencyCode'));
        }

        // Format and return
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        return $dom->saveXML();
    }
}
