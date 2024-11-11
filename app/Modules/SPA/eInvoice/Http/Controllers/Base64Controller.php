<?php

namespace App\Modules\SPA\eInvoice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use SimpleXMLElement;

class Base64Controller extends Controller
{
    private $parsedData;

    public function encode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'file',
                'max:300',
                function ($attribute, $value, $fail) {
                    $mimeType = $value->getMimeType();
                    $extension = strtolower($value->getClientOriginalExtension());
                
                    if ($extension === 'json' && !in_array($mimeType, ['application/json', 'text/plain'])) {
                        $fail('The file must be a valid JSON file.');
                    }
                
                    if ($extension === 'xml' && !in_array($mimeType, [
                        'application/xml',
                        'text/xml',
                        'text/plain',
                        'application/x-xml',
                        'text/html' 
                    ])) {
                        $fail('The file must be a valid XML file.');
                    }
                
                    // Validate JSON content
                    if ($extension === 'json') {
                        $content = file_get_contents($value->getRealPath());
                        json_decode($content);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $fail('The file must contain valid JSON content.');
                        }
                    }
                },
            ],
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

        // Minify based on format
        if ($request->input('format') === 'JSON') {
            $jsonData = json_decode($content, true);
            if ($jsonData === null) {
                return response()->json([
                    'error' => 'Invalid JSON content',
                    'status' => 400
                ], 400);
            }
            $content = json_encode($jsonData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            $this->parsedData = $jsonData;
        } else { // XML
            try {
                $xml = new SimpleXMLElement($content);
                // Remove whitespace and format XML
                $dom = new \DOMDocument('1.0');
                $dom->loadXML($xml->asXML());
                $dom->preserveWhiteSpace = false;
                $dom->formatOutput = false;
                $content = $dom->saveXML($dom->documentElement, LIBXML_NOEMPTYTAG);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Invalid XML format',
                    'status' => 400
                ], 400);
            }
        }

        $encoded = base64_encode($content);
        $hash = hash('sha256', $content);

        // Extract codeNumber based on format
        $codeNumber = null;
        if ($request->input('format') === 'JSON') {
            $jsonData = $this->parsedData ?? json_decode($content, true);
            $codeNumber = $jsonData['Invoice'][0]['ID'][0]['_'] ?? null;
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
                'codeNumber' => $codeNumber
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
