<?php

namespace App\Modules\SPA\eInvoice\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SPA\eInvoice\Config\lhdn as LHDN;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

abstract class BaseApiController extends Controller
{
    protected $baseUrl;
    protected $clientId;
    protected $clientSecret;

    public function __construct()
    {
        $this->baseUrl = LHDN::getApiBaseUrl();
        $this->clientId = LHDN::getClientId();
        $this->clientSecret = LHDN::getClientSecret();
    }

    protected function makeRequest($method, $endpoint, $params = [], $useFormRequest = false)
    {
        $request = Http::withToken(request()->bearerToken());

        if ($useFormRequest) {
            $request = $request->asForm();
        }

        $url = $this->baseUrl . $endpoint;

        if ($method === 'GET' && !empty($params)) {
            $url .= '?' . http_build_query($params);
            $params = []; 
        }

        $response = $request->{strtolower($method)}($url, $params);

        return $this->formatResponse($response);
    }

    protected function formatResponse($response)
    {
        $customResponse = [
            'api' => 'PPZ Central API: SPA eInvoice',
            'time' => now()->setTimezone('Asia/Kuala_Lumpur')->format('h:i A'),
            'date' => now()->setTimezone('Asia/Kuala_Lumpur')->format('d/m/Y'),
        ];

        return response()->json($customResponse + $response->json());
    }
}
