<?php

namespace App\Modules\SPA\eInvoice\Http\Controllers\LHDN_eInvoicing;

use App\Modules\SPA\eInvoice\Http\Controllers\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubmitDocumentsController extends BaseApiController
{
    private const ALLOWED_FORMATS = ['XML', 'JSON'];
    private const MAX_DOCUMENT_SIZE = 307200; // 300KB in bytes
    private const MAX_SUBMISSION_SIZE = 5242880; // 5MB in bytes
    private const MAX_DOCUMENTS_PER_BATCH = 100;

    public function __invoke(Request $request)
    {
        // Validate the request payload
        $validator = Validator::make($request->all(), [
            'documents' => ['required', 'array', 'max:' . self::MAX_DOCUMENTS_PER_BATCH],
            'documents.*.format' => ['required', 'string', 'in:' . implode(',', self::ALLOWED_FORMATS)],
            'documents.*.document' => ['required', 'string'],
            'documents.*.documentHash' => ['required', 'string'],
            'documents.*.codeNumber' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'status' => 400
            ], 400);
        }

        $totalSize = 0;
        foreach ($request->documents as $index => $doc) {
            // Verify base64 and size constraints
            $decodedDocument = base64_decode($doc['document'], true);
            if ($decodedDocument === false) {
                return response()->json([
                    'error' => "Invalid base64 encoded document at index $index",
                    'status' => 400
                ], 400);
            }

            // Check individual document size
            $documentSize = strlen($decodedDocument);
            if ($documentSize > self::MAX_DOCUMENT_SIZE) {
                return response()->json([
                    'error' => "Document at index $index exceeds 300KB limit",
                    'status' => 400
                ], 400);
            }

            // Verify hash
            $calculatedHash = hash('sha256', $decodedDocument);
            if ($calculatedHash !== $doc['documentHash']) {
                return response()->json([
                    'error' => "Document hash mismatch at index $index",
                    'status' => 400
                ], 400);
            }

            $totalSize += $documentSize;
            if ($totalSize > self::MAX_SUBMISSION_SIZE) {
                return response()->json([
                    'error' => 'Total submission size exceeds 5MB limit',
                    'status' => 400
                ], 400);
            }
        }

        // Forward to LHDN API
        return $this->makeRequest(
            'POST',
            '/api/v1.0/documents',
            $request->all(),
            false,
            ['Content-Type' => 'application/json']
        );
    }
}