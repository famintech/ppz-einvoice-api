<?php

use Illuminate\Support\Facades\Route;
use App\Modules\SPA\eInvoice\Http\Controllers\Platform\LoginTaxpayerController;
use App\Modules\SPA\eInvoice\Http\Controllers\Platform\GetAllDocumentTypesController;
use App\Modules\SPA\eInvoice\Http\Controllers\Platform\GetDocumentTypeController;
use App\Modules\SPA\eInvoice\Http\Controllers\Platform\GetDocumentTypeVersionController;
use App\Modules\SPA\eInvoice\Http\Controllers\Platform\GetNotificationsController;

Route::prefix('spa/einvoice')->group(function () {
    Route::get('/login-taxpayer', LoginTaxpayerController::class);
    Route::get('/document-types', GetAllDocumentTypesController::class);
    Route::get('/document-type/{id}', GetDocumentTypeController::class);
    Route::get('/document-type/{id}/version/{vid}', GetDocumentTypeVersionController::class);
    Route::get('/notifications/taxpayer', GetNotificationsController::class);
});
