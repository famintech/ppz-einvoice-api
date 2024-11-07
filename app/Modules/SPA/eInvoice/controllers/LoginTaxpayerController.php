<?php

namespace app\Modules\SPA\eInvoice\Controllers;
use app\Http\Controllers\Controller;

class LoginTaxpayerController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'API v1 is working'
        ]);
    }
}
