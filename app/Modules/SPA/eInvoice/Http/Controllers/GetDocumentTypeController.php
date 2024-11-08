<?php

namespace App\Modules\SPA\eInvoice\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SPA\eInvoice\Config\lhdn as LHDN;
use Illuminate\Support\Facades\Http;

class GetDocumentTypeController extends Controller
{
    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = LHDN::getApiBaseUrl();
    }

    public function __invoke(int $id)
    {
        $response = Http::withToken(request()->bearerToken())
            ->get($this->baseUrl . '/api/v1.0/documenttypes/' . $id);

        $customResponse = [
            'api' => 'PPZ Central API: SPA eInvoice ',
            'time' => now()->setTimezone('Asia/Kuala_Lumpur')->format('h:i A'),
            'date' => now()->setTimezone('Asia/Kuala_Lumpur')->format('d/m/Y'),
        ];

        $finalResponse = $customResponse + $response->json();

        return response()->json($finalResponse);
    }
}