<?php

namespace App\Modules\SPA\eInvoice\Http\Controllers;

use Illuminate\Http\Request;

class GetNotificationsController extends BaseApiController
{
    public function __invoke(Request $request)
    {
        $queryParams = array_filter([
            'dateFrom' => $request->dateFrom,
            'dateTo' => $request->dateTo,
            'type' => $request->type,
            'language' => $request->language,
            'status' => $request->status,
            'pageNo' => $request->pageNo,
            'pageSize' => $request->pageSize,
        ]);

        return $this->makeRequest('GET', '/api/v1.0/notifications/taxpayer', $queryParams);
    }
}