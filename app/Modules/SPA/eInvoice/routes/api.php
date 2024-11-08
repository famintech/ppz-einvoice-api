<?php

use Illuminate\Support\Facades\Route;
use App\Modules\SPA\eInvoice\Http\Controllers\LoginTaxpayerController;
use App\Modules\SPA\eInvoice\Http\Controllers\GetAllDocumentTypesController;
use App\Modules\SPA\eInvoice\Http\Controllers\GetDocumentTypeController;
use App\Modules\SPA\eInvoice\Http\Controllers\GetDocumentTypeVersionController;
use App\Modules\SPA\eInvoice\Http\Controllers\GetNotificationsController;

Route::prefix('spa/einvoice')->group(function () {
    Route::get('/login-taxpayer', LoginTaxpayerController::class);
    Route::get('/document-types', GetAllDocumentTypesController::class);
    Route::get('/document-type/{id}', GetDocumentTypeController::class);
    Route::get('/document-type/{id}/version/{vid}', GetDocumentTypeVersionController::class);
    Route::get('/notifications/taxpayer', GetNotificationsController::class);
});
