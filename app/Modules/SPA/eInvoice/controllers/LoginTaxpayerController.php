<?php

namespace App\Modules\SPA\eInvoice\Controllers;
use App\Http\Controllers\Controller;

class LoginTaxpayerController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'API v1 is working'
        ]);
    }
}