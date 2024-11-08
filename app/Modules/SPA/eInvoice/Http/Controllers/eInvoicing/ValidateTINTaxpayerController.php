<?php

namespace App\Modules\SPA\eInvoice\Http\Controllers\eInvoicing;

use App\Modules\SPA\eInvoice\Http\Controllers\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ValidateTINTaxpayerController extends BaseApiController
{
    private const ALLOWED_ID_TYPES = ['NRIC', 'PASSPORT', 'BRN', 'ARMY'];

    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'tin' => 'required|string',
            'idType' => ['required', Rule::in(self::ALLOWED_ID_TYPES)],
            'idValue' => 'required|string',
        ]);

        $queryParams = [
            'idType' => $validated['idType'],
            'idValue' => $validated['idValue']
        ];

        return $this->makeRequest(
            'GET',
            "/api/v1/spa/einvoice/lhdn/einvoicing/validate-tin-taxpayer/{$validated['tin']}", 
            $queryParams
        );
    }
}