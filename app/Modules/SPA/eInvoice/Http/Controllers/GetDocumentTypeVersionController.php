<?php

namespace App\Modules\SPA\eInvoice\Http\Controllers;

class GetDocumentTypeVersionController extends BaseApiController
{
    public function __invoke(int $id, int $vid)
    {
        return $this->makeRequest('GET', "/api/v1.0/documenttypes/{$id}/versions/{$vid}");
    }
}