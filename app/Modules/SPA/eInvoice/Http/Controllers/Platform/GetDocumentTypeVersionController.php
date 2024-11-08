<?php

namespace App\Modules\SPA\eInvoice\Http\Controllers\Platform;

use App\Modules\SPA\eInvoice\Http\Controllers\BaseApiController;

class GetDocumentTypeVersionController extends BaseApiController
{
    public function __invoke(int $id, int $vid)
    {
        return $this->makeRequest('GET', "/api/v1.0/documenttypes/{$id}/versions/{$vid}");
    }
}