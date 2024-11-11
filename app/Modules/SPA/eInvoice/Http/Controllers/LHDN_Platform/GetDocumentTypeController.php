<?php

namespace App\Modules\SPA\eInvoice\Http\Controllers\LHDN_Platform;

use App\Modules\SPA\eInvoice\Http\Controllers\BaseApiController;

class GetDocumentTypeController extends BaseApiController
{
    public function __invoke(int $id)
    {
        return $this->makeRequest('GET', "/api/v1.0/documenttypes/{$id}");
    }
}