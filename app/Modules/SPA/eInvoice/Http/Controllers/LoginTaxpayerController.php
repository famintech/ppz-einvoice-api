<?php

namespace App\Modules\SPA\eInvoice\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SPA\eInvoice\config\LHDN;
use Illuminate\Support\Facades\Http;

class LoginTaxpayerController extends Controller
{
    private $baseUrl;
    private $clientId;
    private $clientSecret;

    public function __construct()
    {
        $this->baseUrl = LHDN::getApiBaseUrl();
        $this->clientId = LHDN::getClientId();
        $this->clientSecret = LHDN::getClientSecret();
    }

    public function login()
    {
        $response = Http::asForm()->post($this->baseUrl . '/connect/token', [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'client_credentials',
            'scope' => 'InvoicingAPI'
        ]);

        return response()->json($response->json());
    }
}
