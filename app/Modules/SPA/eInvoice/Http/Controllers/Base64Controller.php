<?php

namespace App\Modules\SPA\eInvoice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use SimpleXMLElement;

class Base64Controller extends Controller
{
    public function encode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:json,xml|max:300', 
            'format' => 'required|in:XML,JSON'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'status' => 400
            ], 400);
        }

        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());
        $encoded = base64_encode($content);
        $hash = hash('sha256', $content);

        // Extract codeNumber based on format
        $codeNumber = null;
        if ($request->input('format') === 'JSON') {
            $jsonData = json_decode($content, true);
            $codeNumber = $jsonData['Invoice']['ID']['_'] ?? null;
        } else { // XML
            try {
                $xml = new SimpleXMLElement($content);
                $xml->registerXPathNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
                $idElements = $xml->xpath('//cbc:ID');
                $codeNumber = (string)($idElements[0] ?? '');
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Invalid XML format',
                    'status' => 400
                ], 400);
            }
        }

        if (!$codeNumber) {
            return response()->json([
                'error' => 'Could not extract Invoice ID from document',
                'status' => 400
            ], 400);
        }

        // Prepare document for LHDN submission
        return response()->json([
            'documents' => [
                'format' => $request->input('format'),
                'document' => $encoded,
                'documentHash' => $hash,
                'codeNumber' => $file->getClientOriginalName()
            ],
            'metadata' => [
                'originalSize' => strlen($content),
                'encodedSize' => strlen($encoded),
                'filename' => $file->getClientOriginalName()
            ]
        ]);
    }

    public function decode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'base64' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'status' => 400
            ], 400);
        }

        $decoded = base64_decode($request->input('base64'), true);
        
        if ($decoded === false) {
            return response()->json([
                'error' => 'Invalid base64 string',
                'status' => 400
            ], 400);
        }

        return response()->json([
            'content' => $decoded,
            'hash' => hash('sha256', $decoded),
            'size' => strlen($decoded)
        ]);
    }
}