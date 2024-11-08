<?php

use Illuminate\Support\Facades\Route;
use App\Modules\SPA\eInvoice\Http\Controllers\LoginTaxpayerController;
use App\Modules\SPA\eInvoice\Http\Controllers\GetAllDocumentTypesController;
use App\Modules\SPA\eInvoice\Http\Controllers\GetDocumentTypeController;

Route::prefix('spa/einvoice')->group(function () {
    Route::get('/login-taxpayer', LoginTaxpayerController::class);
    Route::get('/document-types', GetAllDocumentTypesController::class);
    Route::get('/document-type/{id}', GetDocumentTypeController::class);
});
