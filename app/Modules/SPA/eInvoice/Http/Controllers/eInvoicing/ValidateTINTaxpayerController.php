<?php

namespace App\Modules\SPA\eInvoice\Http\Controllers\eInvoicing;

use App\Modules\SPA\eInvoice\Http\Controllers\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ValidateTINTaxpayerController extends BaseApiController
{
    private const ALLOWED_ID_TYPES = ['NRIC', 'PASSPORT', 'BRN', 'ARMY'];

    public function __invoke(Request $request, string $tin)
    {
        $validated = $request->validate([
            'tin' => ['required', 'string'],
            'idType' => ['required', Rule::in(self::ALLOWED_ID_TYPES)],
            'idValue' => ['required', 'string'],
        ]);

        return $this->makeRequest(
            'GET',
            "/api/v1.0/taxpayer/validate/{$tin}",
            [
                'idType' => $request->idType,
                'idValue' => $request->idValue
            ]
        );
    }
}