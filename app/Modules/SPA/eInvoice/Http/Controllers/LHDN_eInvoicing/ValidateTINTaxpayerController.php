<?php

namespace App\Modules\SPA\eInvoice\Http\Controllers\LHDN_eInvoicing;

use App\Modules\SPA\eInvoice\Http\Controllers\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ValidateTINTaxpayerController extends BaseApiController
{
    private const ALLOWED_ID_TYPES = ['NRIC', 'PASSPORT', 'BRN', 'ARMY'];

    public function __invoke(string $tin, string $idType, string $idValue)
    {
        $validated = validator([
            'tin' => $tin,
            'idType' => $idType,
            'idValue' => $idValue
        ], [
            'tin' => 'required|string',
            'idType' => ['required', Rule::in(self::ALLOWED_ID_TYPES)],
            'idValue' => 'required|string',
        ])->validate();

        $queryParams = [
            'idType' => $validated['idType'],
            'idValue' => $validated['idValue']
        ];

        return $this->makeRequest(
            'GET',
            "/api/v1.0/taxpayer/validate/{$validated['tin']}",
            $queryParams
        );
    }
}