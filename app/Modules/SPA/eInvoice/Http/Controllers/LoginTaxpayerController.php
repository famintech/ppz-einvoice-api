<?php

namespace App\Modules\SPA\eInvoice\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SPA\eInvoice\Config\LHDN;

class LoginTaxpayerController extends Controller
{
    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = LHDN::getApiBaseUrl();
    }

    public function index()
    {
        return response()->json([
            'base_url' => $this->baseUrl,
            'message' => 'API v1 is working'
        ]);
    }
}
