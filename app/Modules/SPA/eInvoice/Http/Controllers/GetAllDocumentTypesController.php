<?php

namespace App\Modules\SPA\eInvoice\Http\Controllers;

class GetAllDocumentTypesController extends BaseApiController
{
    public function __invoke()
    {
        return $this->makeRequest('GET', '/api/v1.0/documenttypes');
    }
}