<?php

namespace App\Modules\SPA\eInvoice\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SPA\eInvoice\Config\lhdn as LHDN;
use Illuminate\Support\Facades\Http;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="PPZ SPA eInvoice API Documentation",
 *     description="API documentation for PPZ SPA eInvoice system"
 * )
 * 
 * @OA\Tag(
 *     name="SPA"
 * )
 * 
 * @OA\Tag(
 *     name="SPA/eInvoice",
 * )
 * 
 * @OA\Tag(
 *     name="SPA/eInvoice/LHDN Platform API",
 * )
 */
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

   /**
     * @OA\Get(
     *     path="/api/spa/einvoice/login-taxpayer",
     *     summary="Login LHDN Taxpayer and get access token",
     *     tags={"SPA/eInvoice/LHDN Platform API"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", description="Encoded JWT token structure that contains the fields of the issued token", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIs..."),
     *             @OA\Property(property="token_type", type="string", description="Solution in this case returns only Bearer authentication tokens", example="Bearer"),
     *             @OA\Property(property="expires_in", type="integer", description="The lifetime of the access token defined in seconds", example=3600),
     *             @OA\Property(property="scope", type="string", description="Optional if matches the requested scope. Otherwise contains information on scope granted to token", example="InvoicingAPI")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", description="Error type", example="invalid_request"),
     *             @OA\Property(property="error_description", type="string", description="Detailed error message", example="User blocked"),
     *             @OA\Property(property="error_uri", type="string", description="Optional URI with more error details", example="https://api.myinvois.hasil.gov.my/docs/errors/400")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     * )
     */
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
