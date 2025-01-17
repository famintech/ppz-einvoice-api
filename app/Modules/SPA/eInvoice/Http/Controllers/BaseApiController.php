<?php

namespace App\Modules\SPA\eInvoice\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SPA\eInvoice\Config\lhdn as LHDN;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

    protected function makeRequest($method, $endpoint, $params = [], $useFormRequest = false, $headers = [])
    {
        $request = Http::withToken(request()->bearerToken());

        // Add custom headers
        foreach ($headers as $key => $value) {
            $request = $request->withHeaders([$key => $value]);
        }

        if ($useFormRequest) {
            $request = $request->asForm();
        }

        $url = $this->baseUrl . $endpoint;

        Log::info('Making API request', [
            'method' => $method,
            'url' => $url,
            'headers' => $headers,
            'isRawBody' => !is_array($params)
        ]);

        // Handle raw body vs array params
        $response = is_array($params)
            ? $request->{strtolower($method)}($url, $params)
            : $request->{strtolower($method)}($url, [], $params);

        if (!$response->successful()) {
            Log::error('API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'url' => $url
            ]);
        }

        return $this->formatResponse($response);
    }

    protected function formatResponse($response)
    {
        $customResponse = [
            'api' => 'PPZ Central API: SPA eInvoice',
            'time' => now()->setTimezone('Asia/Kuala_Lumpur')->format('h:i A'),
            'date' => now()->setTimezone('Asia/Kuala_Lumpur')->format('d/m/Y'),
        ];

        if (!$response->successful()) {
            return response()->json([
                ...$customResponse,
                'error' => $response->json() ?? $response->body(),
                'status' => $response->status()
            ], $response->status());
        }

        $responseData = $response->json() ?? [];

        return response()->json([
            ...$customResponse,
            'data' => $responseData,
            'status' => $response->status()
        ]);
    }
}
