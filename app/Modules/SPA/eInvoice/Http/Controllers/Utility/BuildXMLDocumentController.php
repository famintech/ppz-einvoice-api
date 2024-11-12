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
        // Add AllowanceCharge for additional discount if provided
        if ($request->input('additionalDiscountAmount')) {
            $allowanceCharge = $xml->addChild('cac:AllowanceCharge', null, self::XML_NAMESPACES['xmlns:cac']);
            $allowanceCharge->addChild('cbc:ChargeIndicator', 'false', self::XML_NAMESPACES['xmlns:cbc']);
            $allowanceCharge->addChild('cbc:AllowanceChargeReason', $request->input('additionalDiscountReason'), self::XML_NAMESPACES['xmlns:cbc']);
            $amount = $allowanceCharge->addChild('cbc:Amount', $request->input('additionalDiscountAmount'), self::XML_NAMESPACES['xmlns:cbc']);
            $amount->addAttribute('currencyID', $request->input('currencyCode'));
        }

        // Add AllowanceCharge for additional fee if provided
        if ($request->input('additionalFeeAmount')) {
            $allowanceCharge = $xml->addChild('cac:AllowanceCharge', null, self::XML_NAMESPACES['xmlns:cac']);
            $allowanceCharge->addChild('cbc:ChargeIndicator', 'true', self::XML_NAMESPACES['xmlns:cbc']);
            $allowanceCharge->addChild('cbc:AllowanceChargeReason', $request->input('additionalFeeReason'), self::XML_NAMESPACES['xmlns:cbc']);
            $amount = $allowanceCharge->addChild('cbc:Amount', $request->input('additionalFeeAmount'), self::XML_NAMESPACES['xmlns:cbc']);
            $amount->addAttribute('currencyID', $request->input('currencyCode'));
        }

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
            $prepaidPayment->addChild('cbc:ID', $request->input('prePaymentReference'), self::XML_NAMESPACES['xmlns:cbc']);

            $paidAmount = $prepaidPayment->addChild('cbc:PaidAmount', $request->input('prePaymentAmount'), self::XML_NAMESPACES['xmlns:cbc']);
            $paidAmount->addAttribute('currencyID', $request->input('currencyCode'));

            $prepaidPayment->addChild('cbc:PaidDate', $request->input('prePaymentDate'), self::XML_NAMESPACES['xmlns:cbc']);
            $prepaidPayment->addChild('cbc:PaidTime', $request->input('prePaymentTime'), self::XML_NAMESPACES['xmlns:cbc']);
        }

        // Add Billing Reference if provided
        if ($request->input('billReferenceNumber')) {
            $billingReference = $xml->addChild('cac:BillingReference', null, self::XML_NAMESPACES['xmlns:cac']);
            $additionalDocumentReference = $billingReference->addChild('cac:AdditionalDocumentReference', null, self::XML_NAMESPACES['xmlns:cac']);
            $additionalDocumentReference->addChild('cbc:ID', $request->input('billReferenceNumber'), self::XML_NAMESPACES['xmlns:cbc']);
        }

        // Add CustomsImportForm reference if provided
        if ($request->input('customsFormReference')) {
            $references = explode(',', $request->input('customsFormReference'));
            foreach ($references as $reference) {
                $additionalDocumentReference = $xml->addChild('cac:AdditionalDocumentReference', null, self::XML_NAMESPACES['xmlns:cac']);
                $additionalDocumentReference->addChild('cbc:ID', $reference, self::XML_NAMESPACES['xmlns:cbc']);
                $additionalDocumentReference->addChild('cbc:DocumentType', 'CustomsImportForm', self::XML_NAMESPACES['xmlns:cbc']);
            }
        }

        // Add K2 Customs Form reference if provided
        if ($request->input('customsFormK2Reference')) {
            $references = explode(',', $request->input('customsFormK2Reference'));
            foreach ($references as $reference) {
                $additionalDocumentReference = $xml->addChild('cac:AdditionalDocumentReference', null, self::XML_NAMESPACES['xmlns:cac']);
                $additionalDocumentReference->addChild('cbc:ID', $reference, self::XML_NAMESPACES['xmlns:cbc']);
                $additionalDocumentReference->addChild('cbc:DocumentType', 'K2', self::XML_NAMESPACES['xmlns:cbc']);
            }
        }

        // Add Shipping Details if provided
        if ($request->input('shippingDetails')) {
            $delivery = $xml->addChild('cac:Delivery', null, self::XML_NAMESPACES['xmlns:cac']);

            // Add Shipment section
            $shipment = $delivery->addChild('cac:Shipment', null, self::XML_NAMESPACES['xmlns:cac']);
            $shipment->addChild('cbc:ID', $request->input('shippingDetails.referenceNumber'), self::XML_NAMESPACES['xmlns:cbc']);

            // Add FreightAllowanceCharge
            $freightAllowanceCharge = $shipment->addChild('cac:FreightAllowanceCharge', null, self::XML_NAMESPACES['xmlns:cac']);
            $freightAllowanceCharge->addChild('cbc:ChargeIndicator', $request->input('shippingDetails.chargeIndicator') ? 'true' : 'false', self::XML_NAMESPACES['xmlns:cbc']);

            $amount = $freightAllowanceCharge->addChild('cbc:Amount', $request->input('shippingDetails.amount'), self::XML_NAMESPACES['xmlns:cbc']);
            $amount->addAttribute('currencyID', $request->input('currencyCode'));

            if ($request->input('shippingDetails.description')) {
                $freightAllowanceCharge->addChild('cbc:AllowanceChargeReason', $request->input('shippingDetails.description'), self::XML_NAMESPACES['xmlns:cbc']);
            }
        }

        // Add Incoterms if provided
        if ($request->input('incoterms')) {
            $additionalDocumentReference = $xml->addChild('cac:AdditionalDocumentReference', null, self::XML_NAMESPACES['xmlns:cac']);
            $additionalDocumentReference->addChild('cbc:ID', $request->input('incoterms'), self::XML_NAMESPACES['xmlns:cbc']);
        }

        // Add Free Trade Agreement if provided
        if ($request->input('freeTradeAgreement')) {
            $additionalDocumentReference = $xml->addChild('cac:AdditionalDocumentReference', null, self::XML_NAMESPACES['xmlns:cac']);
            $additionalDocumentReference->addChild('cbc:ID', 'FTA', self::XML_NAMESPACES['xmlns:cbc']);
            $additionalDocumentReference->addChild('cbc:DocumentType', 'FreeTradeAgreement', self::XML_NAMESPACES['xmlns:cbc']);

            if ($request->input('freeTradeAgreement.description')) {
                $additionalDocumentReference->addChild('cbc:DocumentDescription', $request->input('freeTradeAgreement.description'), self::XML_NAMESPACES['xmlns:cbc']);
            }
        }

        // Add Authorization Number if provided
        if ($request->input('authorisationNumber')) {
            $accountingSupplierParty = $xml->addChild('cac:AccountingSupplierParty', null, self::XML_NAMESPACES['xmlns:cac']);
            $additionalAccountId = $accountingSupplierParty->addChild('cbc:AdditionalAccountID', $request->input('authorisationNumber'), self::XML_NAMESPACES['xmlns:cbc']);
            $additionalAccountId->addAttribute('schemeAgencyName', 'CertEx');
        }

        // Add Delivery information if shipping recipient is provided
        if ($request->input('shippingRecipientName')) {
            $delivery = $xml->addChild('cac:Delivery', null, self::XML_NAMESPACES['xmlns:cac']);

            // Add DeliveryParty
            $deliveryParty = $delivery->addChild('cac:DeliveryParty', null, self::XML_NAMESPACES['xmlns:cac']);

            // Add PartyIdentification based on registration type
            if ($request->input('shippingRecipientRegistration')) {
                $partyIdentification = $deliveryParty->addChild('cac:PartyIdentification', null, self::XML_NAMESPACES['xmlns:cac']);
                $id = $partyIdentification->addChild('cbc:ID', $request->input('shippingRecipientRegistration.number'), self::XML_NAMESPACES['xmlns:cbc']);

                switch ($request->input('shippingRecipientRegistration.type')) {
                    case 'NRIC':
                        $id->addAttribute('schemeID', 'NRIC');
                        break;
                    case 'BRN':
                        $id->addAttribute('schemeID', 'BRN');
                        break;
                    case 'PASSPORT':
                        $id->addAttribute('schemeID', 'PASSPORT');
                        break;
                    case 'ARMY':
                        $id->addAttribute('schemeID', 'ARMY');
                        break;
                }
            }

            // Add PartyIdentification for TIN if provided
            if ($request->input('shippingRecipientTIN')) {
                $partyIdentification = $deliveryParty->addChild('cac:PartyIdentification', null, self::XML_NAMESPACES['xmlns:cac']);
                $id = $partyIdentification->addChild('cbc:ID', $request->input('shippingRecipientTIN'), self::XML_NAMESPACES['xmlns:cbc']);
                $id->addAttribute('schemeID', 'TIN');
            }

            // Add PartyIdentification for BRN if provided
            if ($request->input('shippingRecipientBRN')) {
                $partyIdentification = $deliveryParty->addChild('cac:PartyIdentification', null, self::XML_NAMESPACES['xmlns:cac']);
                $id = $partyIdentification->addChild('cbc:ID', $request->input('shippingRecipientBRN'), self::XML_NAMESPACES['xmlns:cbc']);
                $id->addAttribute('schemeID', 'BRN');
            }

            // Add PartyLegalEntity for recipient name
            $partyLegalEntity = $deliveryParty->addChild('cac:PartyLegalEntity', null, self::XML_NAMESPACES['xmlns:cac']);
            $partyLegalEntity->addChild('cbc:RegistrationName', $request->input('shippingRecipientName'), self::XML_NAMESPACES['xmlns:cbc']);

            // Add PostalAddress if provided
            if ($request->input('shippingRecipientAddress')) {
                $postalAddress = $deliveryParty->addChild('cac:PostalAddress', null, self::XML_NAMESPACES['xmlns:cac']);

                // Add mandatory Line element (line0)
                $addressLine = $postalAddress->addChild('cac:AddressLine', null, self::XML_NAMESPACES['xmlns:cac']);
                $addressLine->addChild('cbc:Line', $request->input('shippingRecipientAddress.line0'), self::XML_NAMESPACES['xmlns:cbc']);

                // Add optional address lines if provided
                if ($request->input('shippingRecipientAddress.line1')) {
                    $addressLine = $postalAddress->addChild('cac:AddressLine', null, self::XML_NAMESPACES['xmlns:cac']);
                    $addressLine->addChild('cbc:Line', $request->input('shippingRecipientAddress.line1'), self::XML_NAMESPACES['xmlns:cbc']);
                }

                if ($request->input('shippingRecipientAddress.line2')) {
                    $addressLine = $postalAddress->addChild('cac:AddressLine', null, self::XML_NAMESPACES['xmlns:cac']);
                    $addressLine->addChild('cbc:Line', $request->input('shippingRecipientAddress.line2'), self::XML_NAMESPACES['xmlns:cbc']);
                }

                // Add mandatory CityName
                $postalAddress->addChild('cbc:CityName', $request->input('shippingRecipientAddress.cityName'), self::XML_NAMESPACES['xmlns:cbc']);

                // Add optional PostalZone
                if ($request->input('shippingRecipientAddress.postalZone')) {
                    $postalAddress->addChild('cbc:PostalZone', $request->input('shippingRecipientAddress.postalZone'), self::XML_NAMESPACES['xmlns:cbc']);
                }

                // Add mandatory CountrySubentityCode (state)
                $postalAddress->addChild('cbc:CountrySubentityCode', $request->input('shippingRecipientAddress.state'), self::XML_NAMESPACES['xmlns:cbc']);

                // Add mandatory Country
                $country = $postalAddress->addChild('cac:Country', null, self::XML_NAMESPACES['xmlns:cac']);
                $identificationCode = $country->addChild('cbc:IdentificationCode', $request->input('shippingRecipientAddress.country'), self::XML_NAMESPACES['xmlns:cbc']);
                $identificationCode->addAttribute('listID', 'ISO3166-1');
                $identificationCode->addAttribute('listAgencyID', '6');
            }
        }

        // Add Legal Monetary Totals (mandatory)
        $legalMonetaryTotal = $xml->addChild('cac:LegalMonetaryTotal', null, self::XML_NAMESPACES['xmlns:cac']);

        $taxExclusiveAmount = $legalMonetaryTotal->addChild('cbc:TaxExclusiveAmount', $request->input('totalExcludingTax'), self::XML_NAMESPACES['xmlns:cbc']);
        $taxExclusiveAmount->addAttribute('currencyID', $request->input('currencyCode'));

        $taxInclusiveAmount = $legalMonetaryTotal->addChild('cbc:TaxInclusiveAmount', $request->input('totalIncludingTax'), self::XML_NAMESPACES['xmlns:cbc']);
        $taxInclusiveAmount->addAttribute('currencyID', $request->input('currencyCode'));

        // Add AllowanceTotalAmount if discount value is provided
        if ($request->input('totalDiscountValue')) {
            $allowanceTotalAmount = $legalMonetaryTotal->addChild('cbc:AllowanceTotalAmount', $request->input('totalDiscountValue'), self::XML_NAMESPACES['xmlns:cbc']);
            $allowanceTotalAmount->addAttribute('currencyID', $request->input('currencyCode'));
        }

        // Add ChargeTotalAmount if fee/charge amount is provided
        if ($request->input('totalFeeChargeAmount')) {
            $chargeTotalAmount = $legalMonetaryTotal->addChild('cbc:ChargeTotalAmount', $request->input('totalFeeChargeAmount'), self::XML_NAMESPACES['xmlns:cbc']);
            $chargeTotalAmount->addAttribute('currencyID', $request->input('currencyCode'));
        }

        // Add Tax Total (mandatory)
        $taxTotal = $xml->addChild('cac:TaxTotal', null, self::XML_NAMESPACES['xmlns:cac']);
        $taxAmount = $taxTotal->addChild('cbc:TaxAmount', $request->input('totalTaxAmount'), self::XML_NAMESPACES['xmlns:cbc']);
        $taxAmount->addAttribute('currencyID', $request->input('currencyCode'));

        // Add TaxSubtotal elements
        if ($request->input('taxAmountPerType')) {
            $taxSubtotal = $taxTotal->addChild('cac:TaxSubtotal', null, self::XML_NAMESPACES['xmlns:cac']);

            // Add TaxableAmount
            $taxableAmount = $taxSubtotal->addChild('cbc:TaxableAmount', $request->input('taxableAmountPerType'), self::XML_NAMESPACES['xmlns:cbc']);
            $taxableAmount->addAttribute('currencyID', $request->input('currencyCode'));

            // Add TaxAmount
            $subTotalTaxAmount = $taxSubtotal->addChild('cbc:TaxAmount', $request->input('taxAmountPerType'), self::XML_NAMESPACES['xmlns:cbc']);
            $subTotalTaxAmount->addAttribute('currencyID', $request->input('currencyCode'));

            // Add Percent if provided
            if ($request->input('taxPercent')) {
                $taxSubtotal->addChild('cbc:Percent', $request->input('taxPercent'), self::XML_NAMESPACES['xmlns:cbc']);
            }

            // Add TaxCategory
            $taxCategory = $taxSubtotal->addChild('cac:TaxCategory', null, self::XML_NAMESPACES['xmlns:cac']);

            // Add ID (tax type or exemption category)
            if ($request->input('taxExemptionDetails')) {
                $taxCategory->addChild('cbc:ID', 'E', self::XML_NAMESPACES['xmlns:cbc']);
                $taxCategory->addChild('cbc:TaxExemptionReason', $request->input('taxExemptionDetails'), self::XML_NAMESPACES['xmlns:cbc']);
            } else {
                $taxCategory->addChild('cbc:ID', $request->input('taxType'), self::XML_NAMESPACES['xmlns:cbc']);
            }

            // Add TaxScheme
            $taxScheme = $taxCategory->addChild('cac:TaxScheme', null, self::XML_NAMESPACES['xmlns:cac']);
            $taxSchemeId = $taxScheme->addChild('cbc:ID', 'OTH', self::XML_NAMESPACES['xmlns:cbc']);
            $taxSchemeId->addAttribute('schemeID', 'UN/ECE 5153');
            $taxSchemeId->addAttribute('schemeAgencyID', '6');
        }

        // Add PayableRoundingAmount if provided
        if ($request->input('roundingAmount')) {
            $roundingAmount = $legalMonetaryTotal->addChild('cbc:PayableRoundingAmount', $request->input('roundingAmount'), self::XML_NAMESPACES['xmlns:cbc']);
            $roundingAmount->addAttribute('currencyID', $request->input('currencyCode'));
        }

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
