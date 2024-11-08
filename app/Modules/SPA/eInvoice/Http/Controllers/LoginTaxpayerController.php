<?php

namespace App\Modules\SPA\eInvoice\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SPA\eInvoice\Config\lhdn as LHDN;
use Illuminate\Support\Facades\Http;
use GeniusTS\HijriDate\Date;

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

        $customResponse = [
            'api' => 'PPZ Central API',
            'time' => now()->setTimezone('Asia/Kuala_Lumpur')->format('h:i'),
            'date' => now()->setTimezone('Asia/Kuala_Lumpur')->format('d/m/Y'),
            'hijri_date' => Date::now()->setTimezone('Asia/Kuala_Lumpur')->format('d F, Y'),
        ];

        $finalResponse = $customResponse + $response->json();

        return response()->json($finalResponse);
    }
}
