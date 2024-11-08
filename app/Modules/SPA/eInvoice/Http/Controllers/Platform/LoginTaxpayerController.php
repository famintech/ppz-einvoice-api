<?php

namespace App\Modules\SPA\eInvoice\Http\Controllers\Platform;

use App\Modules\SPA\eInvoice\Http\Controllers\BaseApiController;

class LoginTaxpayerController extends BaseApiController
{
    public function __invoke()
    {
        return $this->makeRequest('POST', '/connect/token', [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'client_credentials',
            'scope' => 'InvoicingAPI'
        ], true);
    }
}